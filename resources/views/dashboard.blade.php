<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            {{-- Period toggle --}}
            <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                @foreach([1 => '24h', 7 => '7d', 30 => '30d'] as $days => $label)
                            <a href="{{ route('dashboard', ['period' => $days]) }}" class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors
                                        {{ $period === $days
                    ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                                {{ $label }}
                            </a>
                @endforeach
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="flex items-center gap-3 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-sm text-green-800 dark:text-green-300">
                    <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            <div
                class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('Add New Website') }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Start monitoring a new application.</p>
                    </div>

                    <form action="{{ route('websites.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Site
                                    Name</label>
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

            @if($websites->isNotEmpty())
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Uptime overview</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Last {{ $period === 1 ? '24 hours' : $period . ' days' }} across all sites
                        </p>
                        <div style="position: relative; width: 100%; height: {{ max(80, $websites->count() * 48) }}px;">
                            <canvas id="uptimeChart"></canvas>
                        </div>
                    </div>
                </div>
            @endif

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
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($websites as $website)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors
                                                            {{ !$website->is_monitoring ? 'opacity-60' : '' }}">

                                        {{-- Site name + paused badge + public link --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-2">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $website->name }}</div>
                                                @if(!$website->is_monitoring)
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                                                        Paused
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                <a href="{{ $website->url }}" target="_blank"
                                                    class="hover:text-indigo-500 transition-colors">
                                                    {{ $website->url }}
                                                </a>
                                            </div>
                                            @if($website->is_public)
                                                <a href="{{ route('status.show', $website->public_slug) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-xs text-indigo-500 dark:text-indigo-400 hover:underline mt-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                    </svg>
                                                    Public status page
                                                </a>
                                            @endif
                                        </td>

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
                                            @if(!$website->is_monitoring)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                                    Monitoring paused
                                                </span>
                                            @elseif($website->latestCheck?->is_up)
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

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium
                                                                                    @if($website->uptime_pct >= 99) text-green-600 dark:text-green-400
                                                                                    @elseif($website->uptime_pct >= 90) text-amber-600 dark:text-amber-400
                                                                                    @else text-red-600 dark:text-red-400
                                                                                    @endif">
                                                {{ $website->uptime_pct }}%
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                last {{ $period === 1 ? '24h' : $period . 'd' }}
                                            </div>
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

                                        {{-- Actions --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" @click.outside="open = false"
                                                    class="inline-flex items-center p-1.5 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                                    </svg>
                                                </button>

                                                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="opacity-0 scale-95"
                                                    x-transition:enter-end="opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="opacity-100 scale-100"
                                                    x-transition:leave-end="opacity-0 scale-95"
                                                    class="absolute right-0 z-20 mt-1 w-56 rounded-lg shadow-lg bg-white dark:bg-gray-800 ring-1 ring-gray-200 dark:ring-gray-700 py-1"
                                                    style="display: none;">

                                                    {{-- Pause / resume monitoring --}}
                                                    <form method="POST"
                                                        action="{{ route('websites.toggleMonitoring', $website) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit"
                                                            class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                            @if($website->is_monitoring)
                                                                <svg class="h-4 w-4 text-amber-500" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Pause monitoring
                                                            @else
                                                                <svg class="h-4 w-4 text-green-500" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                Resume monitoring
                                                            @endif
                                                        </button>
                                                    </form>
                                                    {{-- Toggle public status page --}}
                                                    <form method="POST"
                                                        action="{{ route('websites.togglePublic', $website) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit"
                                                            class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                            @if($website->is_public)
                                                                <svg class="h-4 w-4 text-gray-400" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                                </svg>
                                                                Disable status page
                                                            @else
                                                                <svg class="h-4 w-4 text-gray-400" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                </svg>
                                                                Enable status page
                                                            @endif
                                                        </button>
                                                    </form>

                                                    <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>

                                                    {{-- Delete --}}
                                                    <form method="POST" action="{{ route('websites.destroy', $website) }}"
                                                        x-data
                                                        @submit.prevent="if(confirm('Remove {{ addslashes($website->name) }} and all its data? This cannot be undone.')) $el.submit()">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                                stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            Remove website
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
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

            {{-- Incident History --}}
            @if($websites->isNotEmpty())
                <div
                    class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Incident History</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Outages detected across all your monitored
                            sites.</p>
                        @if($allIncidents->isEmpty())
                            <div
                                class="flex items-center gap-3 px-4 py-5 border border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-center justify-center">
                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                        clip-rule="evenodd" />
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No incidents recorded — all sites have been
                                    healthy.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Site</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Status</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Started</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Resolved</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Duration</th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($allIncidents as $incident)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $incident->website->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if($incident->isOngoing())
                                                        <span
                                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                                                            <span class="relative flex h-2 w-2">
                                                                <span
                                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                                <span
                                                                    class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                                            </span>
                                                            Ongoing
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                            Resolved
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $incident->started_at->format('d M, H:i') }}
                                                    <div class="text-xs text-gray-400">{{ $incident->started_at->diffForHumans() }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $incident->resolved_at?->format('d M, H:i') ?? '—' }}
                                                </td>
                                                <td
                                                    class="px-6 py-4 whitespace-nowrap text-sm
                                                                                                                                                                                                                                        {{ $incident->isOngoing() ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-gray-300' }}">
                                                    {{ $incident->durationLabel() }}
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                    {{ $incident->failure_reason ?? '—' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Per-site response time charts --}}
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
                                                        @if($website->uptime_pct >= 99) text-green-600 dark:text-green-400
                                                        @elseif($website->uptime_pct >= 90) text-amber-500 dark:text-amber-400
                                                        @else text-red-600 dark:text-red-400
                                                        @endif">
                                    {{ $website->uptime_pct }}%
                                </p>
                                <p class="text-xs text-gray-400">uptime</p>
                            </div>
                        </div>

                        @if(!$website->is_monitoring)
                            <div
                                class="flex items-center justify-center h-24 text-sm text-amber-600 dark:text-amber-400 border border-dashed border-amber-200 dark:border-amber-800 rounded-lg bg-amber-50 dark:bg-amber-900/10">
                                Monitoring is paused for this site
                            </div>
                        @elseif($website->uptimeChecks()->lastDay()->exists())
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

                @if($websites->isNotEmpty())
                    const uptimeValues = @json($websites->pluck('uptime_pct')->values());
                    const minUptime = Math.min(...uptimeValues);
                    const xMin = minUptime === 100 ? 95 : Math.max(0, minUptime - 5);
                    new Chart(document.getElementById('uptimeChart'), {
                        type: 'bar',
                        data: {
                            labels: @json($websites->pluck('name')),
                            datasets: [{
                                label: 'Uptime %',
                                data: uptimeValues,
                                backgroundColor: @json($websites->map(fn($w) => $w->uptime_pct >= 99 ? '#22c55e' : ($w->uptime_pct >= 90 ? '#f59e0b' : '#ef4444'))),
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
                                    callbacks: { label: ctx => ` ${ctx.parsed.x.toFixed(1)}%` }
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

                @foreach($websites as $website)
                    @if($website->is_monitoring && $website->uptimeChecks()->lastDay()->exists())
                        fetch('{{ route('websites.chart-data', $website) }}')
                            .then(r => r.json())
                            .then(data => {
                                const isSparse = data.response_times.length < 20;
                                const vals = data.response_times.filter(v => v !== null);
                                const maxVal = vals.length ? Math.max(...vals) : 500;
                                const minVal = vals.length ? Math.min(...vals) : 0;
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
                                                min: Math.max(0, Math.floor(minVal * 0.6)),
                                                max: Math.ceil(maxVal * 1.4),
                                                beginAtZero: false,
                                                ticks: { color: labelColor, callback: v => v + ' ms' },
                                                grid: { color: gridColor }
                                            }
                                        }
                                    }
                                });
                            });
                    @endif
                @endforeach

                });
        </script>
    @endpush

</x-app-layout>
