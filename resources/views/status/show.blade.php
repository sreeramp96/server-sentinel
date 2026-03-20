<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $website->name }} — Status</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        body { font-family: "Inter", sans-serif; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased min-h-screen">

    {{-- Header --}}
    <header class="border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
        <div class="max-w-3xl mx-auto px-6 py-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $website->name }}</h1>
                <a href="{{ $website->url }}" target="_blank"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    {{ $website->url }}
                </a>
            </div>
            <a href="{{ url('/') }}"
                class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
                Server Sentinel
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-6 py-10 space-y-8">

        {{-- Current status hero --}}
        <div class="rounded-xl border p-6
            @if($website->latestCheck?->is_up)
                bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800
            @elseif($website->latestCheck?->is_up === false)
                bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800
            @else
                bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700
            @endif">
            <div class="flex items-center gap-4">
                @if($website->latestCheck?->is_up)
                    <div class="relative flex h-5 w-5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-5 w-5 bg-green-500"></span>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-green-800 dark:text-green-300">All systems operational</p>
                        <p class="text-sm text-green-700 dark:text-green-400">Last checked {{ $website->latestCheck->checked_at->diffForHumans() }}</p>
                    </div>
                @elseif($website->latestCheck?->is_up === false)
                    <div class="relative flex h-5 w-5 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500"></span>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-red-800 dark:text-red-300">Service disruption detected</p>
                        <p class="text-sm text-red-700 dark:text-red-400">
                            {{ $website->latestCheck->failure_reason ?? 'Site is not responding' }}
                            · Last checked {{ $website->latestCheck->checked_at->diffForHumans() }}
                        </p>
                    </div>
                @else
                    <div class="h-5 w-5 rounded-full bg-gray-300 dark:bg-gray-600 shrink-0"></div>
                    <div>
                        <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">Awaiting first check</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Monitoring will begin shortly.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Stats row --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center">
                <p class="text-2xl font-bold
                    @if($website->uptime_24h >= 99) text-green-600 dark:text-green-400
                    @elseif($website->uptime_24h >= 90) text-amber-500 dark:text-amber-400
                    @else text-red-600 dark:text-red-400
                    @endif">
                    {{ $website->uptime_24h }}%
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Uptime · 24h</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $website->avg_ms ? $website->avg_ms . ' ms' : '—' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Avg response · 24h</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 text-center">
                <p class="text-2xl font-bold
                    {{ $website->downtime_mins > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                    {{ $website->downtime_mins > 0 ? $website->downtime_mins . ' min' : 'None' }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Downtime · 24h</p>
            </div>
        </div>

        {{-- 90-check uptime heatmap --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-medium text-gray-900 dark:text-white">Uptime — last 90 checks</h2>
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-green-500 inline-block"></span> Up</span>
                    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-red-500 inline-block"></span> Down</span>
                    <span class="flex items-center gap-1"><span class="h-3 w-3 rounded-sm bg-gray-200 dark:bg-gray-700 inline-block"></span> No data</span>
                </div>
            </div>
            <div class="flex gap-1 flex-wrap">
                @forelse($recentChecks as $check)
                    <div title="{{ $check->checked_at->format('d M H:i') }} — {{ $check->is_up ? 'Up' : 'Down' }}{{ $check->response_time_ms ? ' · ' . $check->response_time_ms . 'ms' : '' }}"
                        class="h-6 w-6 rounded-sm transition-opacity hover:opacity-75 cursor-default
                            {{ $check->is_up ? 'bg-green-500' : 'bg-red-500' }}">
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No check data yet.</p>
                @endforelse
            </div>
            @if($recentChecks->isNotEmpty())
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">
                    Oldest on left · newest on right · hover for details
                </p>
            @endif
        </div>

        {{-- Incident History --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Incident history</h2>

            @if($incidents->isEmpty())
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/>
                    </svg>
                    No incidents recorded.
                </div>
            @else
                <div class="space-y-3">
                    @foreach($incidents as $incident)
                        <div class="flex items-start justify-between gap-4 py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div class="flex items-start gap-3">
                                @if($incident->isOngoing())
                                    <span class="relative flex h-2 w-2 mt-1.5 shrink-0">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                @else
                                    <span class="h-2 w-2 rounded-full bg-gray-400 dark:bg-gray-500 mt-1.5 shrink-0"></span>
                                @endif
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $incident->isOngoing() ? 'Active incident' : 'Resolved' }}
                                        @if($incident->failure_reason)
                                            <span class="font-normal text-gray-500 dark:text-gray-400">— {{ $incident->failure_reason }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        Started {{ $incident->started_at->format('d M Y, H:i') }}
                                        @if($incident->resolved_at)
                                            · Resolved {{ $incident->resolved_at->format('H:i') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <span class="text-sm font-medium shrink-0
                                {{ $incident->isOngoing() ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $incident->durationLabel() }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </main>

    <footer class="border-t border-gray-200 dark:border-gray-800 py-6 text-center text-xs text-gray-400 mt-8">
        Powered by <a href="{{ url('/') }}" class="text-indigo-500 hover:underline">Server Sentinel</a>
        · Last updated {{ now()->format('d M Y, H:i') }} UTC
    </footer>

</body>
</html>
