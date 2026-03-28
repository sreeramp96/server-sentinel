<?php

namespace App\Http\Controllers;

// use App\Models\Website;
use App\Http\Requests\StoreWebsiteRequest;
use App\Models\Website;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $period = (int) $request->input('period', 1);

        if (! in_array($period, [1, 7, 30])) {
            $period = 1;
        }

        $websites = $request->user()
            ->websites()
            ->with(['latestCheck', 'incidents'])
            ->withCount([
                'uptimeChecks as total_checks_count' => fn ($q) => $q->lastDays($period),
                'uptimeChecks as up_checks_count' => fn ($q) => $q->lastDays($period)->up(),
                'uptimeChecks as total_24h_count' => fn ($q) => $q->lastDays(1),
                'uptimeChecks as up_24h_count' => fn ($q) => $q->lastDays(1)->up(),
                'uptimeChecks as downtime_mins_count' => fn ($q) => $q->lastDay()->down(),
            ])
            ->withAvg(['uptimeChecks as avg_ms_val' => fn ($q) => $q->lastDays($period)], 'response_time_ms')
            ->get();

        $allIncidents = $websites->flatMap->incidents
            ->sortByDesc('started_at')
            ->take(20);

        return view('dashboard', compact('websites', 'period', 'allIncidents'));
    }

    public function store(StoreWebsiteRequest $request): RedirectResponse
    {
        $request->user()->websites()->create($request->validated());

        return redirect()->route('dashboard')->with('success', 'Site added.');
    }

    public function chartData(Website $website): JsonResponse
    {
        Gate::authorize('view', $website);

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
        Gate::authorize('delete', $website);

        $website->delete();

        return redirect()->route('dashboard')->with('success', 'Website removed.');
    }

    public function toggleMonitoring(Website $website): RedirectResponse
    {
        Gate::authorize('update', $website);

        $website->update(['is_monitoring' => ! $website->is_monitoring]);

        $message = $website->is_monitoring
            ? "Monitoring resumed for {$website->name}."
            : "Monitoring paused for {$website->name}.";

        return redirect()->route('dashboard')->with('success', $message);
    }

    public function togglePublic(Website $website): RedirectResponse
    {
        Gate::authorize('update', $website);

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
