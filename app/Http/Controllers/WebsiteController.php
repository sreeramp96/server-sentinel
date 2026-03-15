<?php

namespace App\Http\Controllers;

// use App\Models\Website;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(): View
    {
        $websites = auth()->user()
            ->websites()                          // assumes User hasMany Website
            ->with('latestCheck')                 // eager load — avoids N+1
            ->get();

        // Attach stats to each website for the view
        $websites->each(function ($website) {
            $website->uptime_24h = $website->uptimePercentage(1);
            $website->uptime_7d = $website->uptimePercentage(7);
            $website->avg_ms = $website->avgResponseTime(1);
            $website->downtime_mins = $website->downtimeMinutes();
        });

        return view('dashboard', compact('websites'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
        ]);

        auth()->user()->websites()->create($validated);

        return redirect()->route('dashboard')->with('success', 'Site added.');
    }

    public function chartData(Website $website): JsonResponse
    {
        // Authorise — users can only see their own sites
        abort_if($website->user_id !== auth()->id(), 403);

        $checks = $website->uptimeChecks()
            ->lastDay()
            ->orderBy('checked_at')
            ->get(['checked_at', 'response_time_ms', 'is_up'])
            ->filter(fn ($c, $i) => $i % 5 === 0)  // thin out for chart
            ->values();

        return response()->json([
            'labels' => $checks->map(fn ($c) => $c->checked_at->format('H:i')),
            'response_times' => $checks->map(fn ($c) => $c->response_time_ms),
            'statuses' => $checks->map(fn ($c) => $c->is_up),
        ]);
    }
}
