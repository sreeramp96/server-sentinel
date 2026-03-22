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
    public function index(Request $request): View
    {
        $period = (int) $request->get('period', 1);

        // Clamp to allowed values only
        if (! in_array($period, [1, 7, 30])) {
            $period = 1;
        }

        $websites = auth()->user()
            ->websites()                          // assumes User hasMany Website
            ->with(['latestCheck', 'incidents' => fn ($q) => $q->whereNull('resolved_at')])              // eager load — avoids N+1
            ->get();

        $websites->each(function ($website) use ($period) {
            $website->uptime_pct = $website->uptimePercentage($period);
            $website->uptime_24h = $website->uptimePercentage(1);
            $website->avg_ms = $website->avgResponseTime($period);
            $website->downtime_mins = $website->downtimeMinutes();
        });

        return view('dashboard', compact('websites', 'period'));
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

    public function destroy(Website $website): RedirectResponse
    {
        abort_if($website->user_id !== auth()->id(), 403);

        $website->delete();

        return redirect()->route('dashboard')->with('success', 'Website removed.');
    }

    public function toggleMonitoring(Website $website): RedirectResponse
    {
        abort_if($website->user_id !== auth()->id(), 403);

        $website->update(['is_monitoring' => ! $website->is_monitoring]);

        $message = $website->is_monitoring
            ? "Monitoring resumed for {$website->name}."
            : "Monitoring paused for {$website->name}.";

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function togglePublic(Website $website): RedirectResponse
    {
        abort_if($website->user_id !== auth()->id(), 403);

        if (! $website->is_public) {
            $website->update([
                'is_public' => true,
                'public_slug' => $website->public_slug ?? $website->generatePublicSlug(),
            ]);
        } else {
            $website->update(['is_public' => false]);
        }

        return redirect()->route('dashboard')->with(
            'success',
            $website->is_public ? 'Status page enabled.' : 'Status page disabled.'
        );
    }
}
