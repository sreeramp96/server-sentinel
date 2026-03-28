<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\View\View;

class StatusPageController extends Controller
{
    public function show(string $slug): View
    {
        $website = Website::query()
            ->where('public_slug', $slug)
            ->where('is_public', true)
            ->withCount([
                'uptimeChecks as total_checks_count' => fn ($q) => $q->lastDays(7),
                'uptimeChecks as up_checks_count' => fn ($q) => $q->lastDays(7)->up(),
                'uptimeChecks as total_24h_count' => fn ($q) => $q->lastDays(1),
                'uptimeChecks as up_24h_count' => fn ($q) => $q->lastDays(1)->up(),
                'uptimeChecks as downtime_mins_count' => fn ($q) => $q->lastDay()->down(),
            ])
            ->withAvg(['uptimeChecks as avg_ms_val' => fn ($q) => $q->lastDays(1)], 'response_time_ms')
            ->firstOrFail();

        $website->uptime_7d = $website->uptime_pct; // period=7 in withCount

        $recentChecks = $website->uptimeChecks()
            ->lastDay()
            ->orderBy('checked_at')
            ->limit(90)
            ->get(['checked_at', 'is_up', 'response_time_ms', 'status_code']);

        $incidents = $website->incidents()->limit(10)->get();

        return view('status.show', compact('website', 'recentChecks', 'incidents'));
    }
}
