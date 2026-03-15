<?php

namespace Database\Seeders;

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
                ['name' => 'Production App',  'url' => 'https://example.com'],
                ['name' => 'Staging Server',  'url' => 'https://staging.example.com'],
                ['name' => 'Marketing Site',  'url' => 'https://marketing.example.com'],
            ])->map(fn ($data) => Website::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'url' => $data['url'],
                'is_active' => true,
            ]));
        }

        // Generate 24 hours of checks, one per minute = 1440 checks per site
        $websites->each(function (Website $website) {

            // Delete existing dummy data for this site first
            $website->uptimeChecks()->delete();

            $baseMs = match ($website->name) {
                'Production App' => 180,   // fast, healthy site
                'Staging Server' => 420,   // slower, less optimised
                'Marketing Site' => 290,   // mid-range
                default => 250,
            };

            // Simulate a realistic outage window for staging
            $outageStart = now()->subHours(6);
            $outageEnd = now()->subHours(5)->subMinutes(23);

            $checks = [];
            $now = now();

            for ($i = 1440; $i >= 0; $i--) {
                $checkedAt = $now->copy()->subMinutes($i);

                // Inject outage for Staging Server only
                $inOutage = $website->name === 'Staging Server'
                    && $checkedAt->between($outageStart, $outageEnd);

                // Add natural noise to response time (±30%)
                $noise = rand(-30, 60);
                $responseMs = $inOutage ? null : max(80, $baseMs + $noise);

                // Occasional slow spike (1 in 40 checks)
                if (! $inOutage && rand(1, 40) === 1) {
                    $responseMs = $baseMs * rand(3, 6);
                }

                $statusCode = $inOutage ? 503 : 200;
                $isUp = ! $inOutage;

                $checks[] = [
                    'website_id' => $website->id,
                    'is_up' => $isUp,
                    'response_time_ms' => $responseMs,
                    'status_code' => $statusCode,
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

            // Sync is_active to last check result
            $website->update(['is_active' => true]);

            $this->command->info("Seeded {$website->name}: 1440 checks");
        });
    }
}
