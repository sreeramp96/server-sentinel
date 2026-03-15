<?php

namespace App\Jobs;

use App\Models\UptimeCheck;
use App\Models\Website;
use App\Notifications\SiteDownNotification;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckWebsite implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;       // don't retry — stale results are worse than no result

    public int $timeout = 15;    // Guzzle timeout + job timeout

    public function __construct(public Website $website) {}

    public function handle(): void
    {
        $responseTimeMs = null;
        $statusCode = null;
        $failureReason = null;
        $isUp = false;

        try {
            $client = new Client;

            $response = $client->get($this->website->url, [
                'timeout' => 10,
                'connect_timeout' => 5,
                'http_errors' => false,   // don't throw on 4xx/5xx — we want to record them
                'verify' => false,   // skip SSL verification for now
                'on_stats' => function (TransferStats $stats) use (&$responseTimeMs) {
                    $responseTimeMs = (int) ($stats->getTransferTime() * 1000);
                },
            ]);

            $statusCode = $response->getStatusCode();
            $isUp = $statusCode >= 200 && $statusCode < 400;

            if (! $isUp) {
                $failureReason = "HTTP {$statusCode}";
            }

        } catch (ConnectException $e) {
            $failureReason = 'Connection failed: '.$e->getMessage();
        } catch (RequestException $e) {
            $failureReason = 'Request failed: '.$e->getMessage();
        } catch (\Throwable $e) {
            $failureReason = 'Unknown error: '.$e->getMessage();
        }

        $check = UptimeCheck::create([
            'website_id' => $this->website->id,
            'is_up' => $isUp,
            'response_time_ms' => $responseTimeMs,
            'status_code' => $statusCode,
            'failure_reason' => $failureReason,
            'checked_at' => now(),
        ]);

        $this->website->update(['is_active' => $isUp]);

        $this->maybeNotify($isUp, $check);
    }

    private function maybeNotify(bool $isUp, UptimeCheck $check): void
    {
        if ($isUp) {
            // Site is up — reset last_notified_at so next outage triggers a fresh alert
            if ($this->website->last_notified_at !== null) {
                $this->website->update(['last_notified_at' => null]);
            }

            return;
        }

        // Site is down — only notify if we haven't already alerted for this outage
        if ($this->website->last_notified_at !== null) {
            return; // already sent alert for this outage, stay quiet
        }

        // Get the website owner and send the notification
        $owner = $this->website->user;

        $owner->notify(new SiteDownNotification($this->website, $check));

        $this->website->update(['last_notified_at' => now()]);
    }
}
