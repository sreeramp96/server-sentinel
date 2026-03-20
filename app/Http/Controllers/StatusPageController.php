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
            ->firstOrFail();

        $website->uptime_24h    = $website->uptimePercentage(1);
        $website->uptime_7d     = $website->uptimePercentage(7);
        $website->avg_ms        = $website->avgResponseTime(1);
        $website->downtime_mins = $website->downtimeMinutes();

        $recentChecks = $website->uptimeChecks()
            ->lastDay()
            ->orderBy('checked_at')
            ->limit(90)
            ->get(['checked_at', 'is_up', 'response_time_ms', 'status_code']);

        $incidents = $website->incidents()->limit(10)->get();

        return view('status.show', compact('website', 'recentChecks', 'incidents'));
    }
}
