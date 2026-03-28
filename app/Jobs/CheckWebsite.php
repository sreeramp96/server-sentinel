<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Models\UptimeCheck;
use App\Models\Website;
use App\Notifications\SiteDownNotification;
use App\Notifications\SiteRecoveredNotification;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class CheckWebsite implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;       // don't retry — stale results are worse than no result

    public int $timeout = 20;    // Job timeout

    public function __construct(public Website $website) {}

    public function handle(): void
    {
        if (! $this->website->is_monitoring) {
            return;
        }

        $responseTimeMs = null;
        $statusCode = null;
        $failureReason = null;
        $isUp = false;

        try {
            $response = Http::timeout(15)
                ->connectTimeout(10)
                ->withOptions([
                    'verify' => false,
                    'allow_redirects' => [
                        'max' => 5,
                        'strict' => false,
                        'referer' => false,
                        'protocols' => ['http', 'https'],
                        'track_redirects' => true,
                    ],
                    'on_stats' => function (TransferStats $stats) use (&$responseTimeMs) {
                        $responseTimeMs = (int) ($stats->getTransferTime() * 1000);
                    },
                ])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; ServerSentinel/1.0; +https://github.com/server-sentinel)',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])
                ->get($this->website->url);

            $statusCode = $response->status();
            $isUp = $response->successful() || $response->redirect();

            if (! $isUp) {
                $failureReason = "HTTP {$statusCode}";
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $failureReason = $this->cleanConnectError($e->getMessage());
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $failureReason = 'Request failed: '.$e->getMessage();
        } catch (\Throwable $e) {
            $failureReason = 'Unknown error: '.$e->getMessage();
        }

        UptimeCheck::create([
            'website_id' => $this->website->id,
            'is_up' => $isUp,
            'response_time_ms' => $responseTimeMs,
            'status_code' => $statusCode,
            'failure_reason' => $failureReason,
            'checked_at' => now(),
        ]);

        $this->website->update(['is_active' => $isUp]);

        $resolvedIncident = $this->handleIncident($isUp, $failureReason);

        $this->maybeNotify($isUp, $resolvedIncident);
    }

    /**
     * Convert verbose cURL error messages into short human-readable strings.
     * e.g. "cURL error 6: Could not resolve host: x.com (...)" → "DNS failure: x.com"
     */
    private function cleanConnectError(string $message): string
    {
        if (str_contains($message, 'Could not resolve host')) {
            preg_match('/Could not resolve host: ([^\s(]+)/', $message, $matches);
            $host = $matches[1] ?? 'unknown';

            return "DNS failure: {$host}";
        }

        if (str_contains($message, 'Connection refused')) {
            return 'Connection refused';
        }

        if (str_contains($message, 'timed out') || str_contains($message, 'Operation timed out')) {
            return 'Connection timed out';
        }

        if (str_contains($message, 'SSL') || str_contains($message, 'certificate')) {
            return 'SSL/certificate error';
        }

        // Fall back to first sentence only — strips the haxx.se URL noise
        return str_contains($message, '(see')
            ? trim(explode('(see', $message)[0])
            : $message;
    }

    private function handleIncident(bool $isUp, ?string $failureReason): ?Incident
    {
        $openIncident = Incident::where('website_id', $this->website->id)
            ->whereNull('resolved_at')
            ->latest('started_at')
            ->first();

        if (! $isUp) {
            if ($openIncident === null) {
                Incident::create([
                    'website_id' => $this->website->id,
                    'started_at' => now(),
                    'failure_reason' => $failureReason,
                ]);
            }

            return null;
        }

        if ($openIncident !== null) {
            $openIncident->update([
                'resolved_at' => now(),
                'duration_minutes' => (int) $openIncident->started_at->diffInMinutes(now()),
            ]);

            return $openIncident->fresh();
        }

        return null;
    }

    private function maybeNotify(bool $isUp, ?Incident $resolvedIncident): void
    {
        $owner = $this->website->user;

        if ($isUp) {
            // Site is up — reset last_notified_at so next outage triggers a fresh alert
            if ($this->website->last_notified_at !== null) {
                if ($resolvedIncident !== null) {
                    $owner->notify(new SiteRecoveredNotification($this->website, $resolvedIncident));
                }

                $this->website->update(['last_notified_at' => null]);
            }

            return;
        }

        // Site is down — only notify if we haven't already alerted for this outage
        if ($this->website->last_notified_at !== null) {
            return; // already sent alert for this outage, stay quiet
        }

        $check = UptimeCheck::where('website_id', $this->website->id)
            ->latest('checked_at')
            ->first();

        $owner->notify(new SiteDownNotification($this->website, $check));

        $this->website->update(['last_notified_at' => now()]);
    }
}
