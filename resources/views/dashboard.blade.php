<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- CARD 1: Add New Website form (unchanged from Week 1) --}}
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Add New Website</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Start monitoring a new application.</p>
                    </div>

                    <form action="{{ route('websites.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site Name</label>
                                <input type="text" name="name" id="name"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 text-base"
                                    placeholder="e.g. My Production App" required>
                            </div>
                            <div>
                                <label for="url"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">URL</label>
                                <input type="url" name="url" id="url"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 text-base"
                                    placeholder="https://example.com" required>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 shadow-sm">
                                Start Monitoring
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- CARD 2: Uptime overview bar chart (NEW — Week 3) --}}
            @if($websites->isNotEmpty())
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Uptime overview</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Last 24 hours across all sites</p>

                        <div style="position: relative; width: 100%; height: {{ max(80, $websites->count() * 48) }}px;">
                            <canvas id="uptimeChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

            {{-- CARD 3: Monitored websites table (updated — Week 2) --}}
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-6">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Monitored Websites</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Overview of all your registered
                                applications.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Site</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        URL</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Uptime</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Response time</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Downtime</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($websites as $website)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">

                                        {{-- Site name + added date --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $website->name }}
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">Added
                                                {{ $website->created_at->diffForHumans() }}
                                            </div>
                                        </td>

                                        {{-- URL --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ $website->url }}" target="_blank"
                                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors flex items-center gap-1">
                                                {{ $website->url }}
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                            </a>
                                        </td>

                                        {{-- Live status from latest check --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($website->latestCheck?->is_up)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">
                                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-500" fill="currentColor"
                                                        viewBox="0 0 8 8">
                                                        <circle cx="4" cy="4" r="3" />
                                                    </svg>
                                                    Up
                                                </span>
                                            @elseif($website->latestCheck?->is_up === false)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-red-500" fill="currentColor"
                                                        viewBox="0 0 8 8">
                                                        <circle cx="4" cy="4" r="3" />
                                                    </svg>
                                                    Down
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-gray-500">Not checked yet</span>
                                            @endif
                                        </td>

                                        {{-- Uptime % with colour coding --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium
                                                        @if($website->uptime_24h >= 99) text-green-600 dark:text-green-400
                                                        @elseif($website->uptime_24h >= 90) text-amber-600 dark:text-amber-400
                                                        @else text-red-600 dark:text-red-400
                                                        @endif">
                                                {{ $website->uptime_24h }}%
                                            </div>
                                            <div class="text-xs text-gray-400">last 24h</div>
                                        </td>

                                        {{-- Avg response time --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ $website->avg_ms ? $website->avg_ms . ' ms' : '—' }}
                                            </div>
                                            <div class="text-xs text-gray-400">avg response</div>
                                        </td>

                                        {{-- Downtime minutes --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div
                                                class="text-sm {{ $website->downtime_mins > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }}">
                                                {{ $website->downtime_mins > 0 ? $website->downtime_mins . ' min' : 'None' }}
                                            </div>
                                            <div class="text-xs text-gray-400">last 24h</div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center justify-center">
                                                <div class="bg-gray-100 dark:bg-gray-700/50 rounded-full p-3 mb-4">
                                                    <svg class="h-8 w-8 text-gray-400 dark:text-gray-500" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                                    </svg>
                                                </div>
                                                <p class="text-base font-medium text-gray-900 dark:text-gray-200">No
                                                    websites monitored</p>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by
                                                    adding a new website above.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- CARDS 4+: Per-site response time charts (NEW — Week 3) --}}
            @foreach($websites as $website)
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-base font-medium text-gray-900 dark:text-white">{{ $website->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Response time — last 24h</p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-semibold
                                        @if($website->uptime_24h >= 99) text-green-600 dark:text-green-400
                                        @elseif($website->uptime_24h >= 90) text-amber-500 dark:text-amber-400
                                        @else text-red-600 dark:text-red-400
                                        @endif">
                                    {{ $website->uptime_24h }}%
                                </p>
                                <p class="text-xs text-gray-400">uptime</p>
                            </div>
                        </div>

                        @if($website->uptimeChecks()->lastDay()->exists())
                            <div style="position: relative; width: 100%; height: 180px;">
                                <canvas id="chart-{{ $website->id }}"></canvas>
                            </div>
                        @else
                            <div
                                class="flex items-center justify-center h-32 text-sm text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
                                No data yet — first check pending
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    {{-- Chart.js scripts --}}
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.min.js"
            integrity="sha512-n/G+dROKbKL3GVngGWmWfwK0yPctjZQM752diVYnXZtD/48agpUKLIn0xDQL9ydZ91x6BiOmTIFwWjjFi2kEFg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const isDark = document.documentElement.classList.contains('dark');
                const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.06)';
                const labelColor = isDark ? '#9ca3af' : '#6b7280';
                const tooltipBg = isDark ? '#1f2937' : '#ffffff';
                const tooltipText = isDark ? '#f3f4f6' : '#111827';

                // ── Uptime overview bar chart ────────────────────────────────
                @if($websites->isNotEmpty())
                    const uptimeValues = @json($websites->pluck('uptime_24h')->values());
                    const minUptime = Math.min(...uptimeValues);
                    const xMin = minUptime === 100 ? 95 : Math.max(0, minUptime - 5);
                    new Chart(document.getElementById('uptimeChart'), {
                        type: 'bar',
                        data: {
                            labels: @json($websites->pluck('name')),
                            datasets: [{
                                label: 'Uptime %',
                                data: @json($websites->pluck('uptime_24h')),
                                backgroundColor: @json(
                                    $websites->map(
                                        fn($w) =>
                                        $w->uptime_24h >= 99 ? '#22c55e' :
                                        ($w->uptime_24h >= 90 ? '#f59e0b' : '#ef4444')
                                    )
                                ),
                                borderRadius: 6,
                                borderSkipped: false,
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: tooltipBg,
                                    titleColor: tooltipText,
                                    bodyColor: tooltipText,
                                    borderColor: gridColor,
                                    borderWidth: 1,
                                    callbacks: {
                                        label: ctx => {
                                            const counts = @json($websites->map(fn($w) => $w->uptimeChecks()->lastDay()->count())->values());
                                            const n = counts[ctx.dataIndex];
                                            return ` ${ctx.parsed.x.toFixed(1)}%  (${n} check${n === 1 ? '' : 's'})`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    min: xMin,
                                    max: 100,
                                    ticks: { color: labelColor, callback: v => v + '%' },
                                    grid: { color: gridColor }
                                },
                                y: {
                                    ticks: { color: labelColor },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                @endif

                // ── Per-site response time line charts ───────────────────────
                @foreach($websites as $website)
                    @if($website->uptimeChecks()->lastDay()->exists())
                        fetch('{{ route('websites.chart-data', $website) }}')
                            .then(r => r.json())
                            .then(data => {
                                const isSparse = data.response_times.length < 20;
                                const vals = data.response_times.filter(v => v !== null);
                                const maxVal = vals.length ? Math.max(...vals) : 500;
                                const minVal = vals.length ? Math.min(...vals) : 0;
                                // give the Y axis breathing room so a flat line isn't at the very top
                                const yMax = Math.ceil(maxVal * 1.4);
                                const yMin = Math.max(0, Math.floor(minVal * 0.6));
                                new Chart(document.getElementById('chart-{{ $website->id }}'), {
                                    type: 'line',
                                    data: {
                                        labels: data.labels,
                                        datasets: [{
                                            label: 'Response time (ms)',
                                            data: data.response_times,
                                            borderColor: '#6366f1',
                                            backgroundColor: 'rgba(99,102,241,0.08)',
                                            borderWidth: 2,
                                            pointRadius: isSparse ? 4 : 0,      // show dots when few points
                                            pointHoverRadius: 5,
                                            pointBackgroundColor: '#6366f1',
                                            tension: isSparse ? 0 : 0.3,        // straight lines when sparse
                                            fill: true,
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: {
                                                backgroundColor: tooltipBg,
                                                titleColor: tooltipText,
                                                bodyColor: tooltipText,
                                                borderColor: gridColor,
                                                borderWidth: 1,
                                                callbacks: {
                                                    label: ctx => ` ${Math.round(ctx.parsed.y)} ms`
                                                }
                                            }
                                        },
                                        scales: {
                                            x: {
                                                ticks: { color: labelColor, maxTicksLimit: 8, autoSkip: true },
                                                grid: { display: false }
                                            },
                                            y: {
                                                min: yMin,
                                                max: yMax,
                                                beginAtZero: false,
                                                ticks: { color: labelColor, callback: v => v + ' ms' },
                                                grid: { color: gridColor }
                                            }
                                        }
                                    }
                                });
                                if (isSparse) {
                                    const notice = document.createElement('p');
                                    notice.style.cssText = 'font-size:12px;color:' + labelColor + ';margin-top:6px;text-align:center';
                                    notice.textContent = `${data.response_times.length} checks recorded — chart will fill in over time`;
                                    document.getElementById('chart-{{ $website->id }}').parentNode.after(notice);
                                }
                            });
                    @endif
                @endforeach

            });
        </script>
    @endpush

</x-app-layout>
