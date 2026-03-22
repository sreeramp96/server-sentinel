<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\UptimeCheck;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Seeder;

class UptimeCheckSeeder extends Seeder
{
    public function run(): void
    {
        // Grab the first user — or create one if running fresh
        $user = User::first() ?? User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create 3 demo websites if none exist
        $websites = Website::where('user_id', $user->id)->get();

        if ($websites->isEmpty()) {
            $websites = collect([
                ['name' => 'GitHub', 'url' => 'https://github.com'],
                ['name' => 'Cloudflare', 'url' => 'https://www.cloudflare.com'],
                ['name' => 'Hacker News', 'url' => 'https://news.ycombinator.com'],
            ])->map(fn ($data) => Website::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'url' => $data['url'],
                'is_active' => true,
                'is_monitoring' => true,
                'is_public' => false,
            ]));
        }

        // Generate 24 hours of checks, one per minute = 1440 checks per site
        $websites->each(function (Website $website) {

            // Delete existing dummy data for this site first
            $website->uptimeChecks()->delete();
            $website->incidents()->delete();

            $baseMs = match ($website->name) {
                'GitHub' => 180,
                'Cloudflare' => 90,
                'Hacker News' => 320,
                default => 250,
            };

            // Outage windows (within last 24h so the dashboard shows them)
            $outages = match ($website->name) {
                'Hacker News' => [
                    ['start' => now()->subHours(6), 'end' => now()->subHours(5)->subMinutes(23)],
                ],
                'GitHub' => [
                    ['start' => now()->subHours(14), 'end' => now()->subHours(13)->subMinutes(45)],
                ],
                default => [],
            };

            $checks = [];
            $now = now();

            for ($i = 1440; $i >= 0; $i--) {
                $checkedAt = $now->copy()->subMinutes($i);

                $inOutage = collect($outages)->contains(
                    fn ($o) => $checkedAt->between($o['start'], $o['end'])
                );

                // Add natural noise to response time (±30%)
                $noise = rand(-30, 60);
                $responseMs = $inOutage ? null : max(50, $baseMs + $noise);

                // Occasional slow spike (1 in 40 checks)
                if (! $inOutage && rand(1, 40) === 1) {
                    $responseMs = $baseMs * rand(3, 6);
                }

                $checks[] = [
                    'website_id' => $website->id,
                    'is_up' => ! $inOutage,
                    'response_time_ms' => $responseMs,
                    'status_code' => $inOutage ? 503 : 200,
                    'failure_reason' => $inOutage ? 'HTTP 503' : null,
                    'checked_at' => $checkedAt,
                    'created_at' => $checkedAt,
                    'updated_at' => $checkedAt,
                ];
            }

            // Bulk insert — much faster than 1440 individual creates
            foreach (array_chunk($checks, 200) as $chunk) {
                UptimeCheck::insert($chunk);
            }

            // Seed incidents from the outage windows
            foreach ($outages as $outage) {
                $duration = (int) $outage['start']->diffInMinutes($outage['end']);

                Incident::create([
                    'website_id' => $website->id,
                    'started_at' => $outage['start'],
                    'resolved_at' => $outage['end'],
                    'failure_reason' => 'HTTP 503',
                    'duration_minutes' => $duration,
                    'created_at' => $outage['start'],
                    'updated_at' => $outage['end'],
                ]);
            }

            $website->update(['is_active' => true]);

            $this->command->info("Seeded {$website->name}: 1440 checks, ".count($outages).' incident(s)');
        });
    }
}
