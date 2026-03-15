<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Server Sentinel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        </script>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <style>
    body {
        font-family: "Inter", sans-serif;
        font-optical-sizing: auto;
        font-weight: <weight>;
        font-style: normal;
    }
</style>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white">

        <header class="w-full relative z-10 px-6 py-6 sm:px-8 lg:px-12 flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
                <span class="font-bold text-xl tracking-tight text-gray-900 dark:text-white">Server Sentinel</span>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-4">
                    <!-- Theme Toggle Button -->
                    <button
                        x-data="{
                            darkMode: localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                            toggle() {
                                this.darkMode = !this.darkMode;
                                if (this.darkMode) {
                                    localStorage.theme = 'dark';
                                    document.documentElement.classList.add('dark');
                                } else {
                                    localStorage.theme = 'light';
                                    document.documentElement.classList.remove('dark');
                                }
                            }
                        }"
                        @click="toggle()"
                        class="p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
                        aria-label="Toggle Dark Mode"
                    >
                        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white transition-colors">
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900">
                                Get Started
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="grow flex items-center justify-center relative overflow-hidden">
            <!-- Background gradients -->
            <div class="absolute top-0 inset-x-0 h-64 bg-linear-to-b from-indigo-50/50 to-transparent dark:from-indigo-900/20 dark:to-transparent z-0"></div>
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-indigo-200/50 dark:bg-indigo-900/20 blur-3xl filter opacity-50 z-0"></div>
            <div class="absolute top-48 -right-24 w-96 h-96 rounded-full bg-purple-200/50 dark:bg-purple-900/20 blur-3xl filter opacity-50 z-0"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-6 lg:px-8 py-24 text-center">
                <div class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium text-indigo-800 bg-indigo-100 dark:bg-indigo-900/50 dark:text-indigo-300 mb-8 ring-1 ring-inset ring-indigo-200 dark:ring-indigo-800/50 shadow-sm">
                    <span class="flex h-2 w-2 rounded-full bg-indigo-600 dark:bg-indigo-400 mr-2"></span>
                    Monitor your infrastructure 24/7
                </div>

                <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-8 text-balance">
                    Keep your websites <br class="hidden md:block" />
                    <span class="text-transparent bg-clip-text bg-linear-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">up and running</span>
                </h1>

                <p class="mt-4 text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-10 text-pretty">
                    Server Sentinel is a beautiful, minimalist monitoring tool that alerts you the second your websites go down. Never lose a customer to downtime again.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all duration-200">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 shadow-md hover:shadow-lg transition-all duration-200">
                            Start for free
                        </a>
                        <a href="#features" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-lg text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 shadow-sm transition-all duration-200">
                            Learn more
                        </a>
                    @endauth
                </div>

                <div class="mt-20 relative mx-auto w-full max-w-4xl pt-8 sm:pt-0">
                    <div class="rounded-xl bg-gray-900/5 dark:bg-white/5 p-2 ring-1 ring-inset ring-gray-900/10 dark:ring-white/10 lg:-m-4 lg:rounded-2xl lg:p-4 backdrop-blur-sm">
                        <div class="rounded-lg bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-gray-900/10 dark:ring-white/10 overflow-hidden">
                            <div class="flex items-center gap-1.5 border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 px-4 py-3">
                                <div class="h-3 w-3 rounded-full bg-red-500"></div>
                                <div class="h-3 w-3 rounded-full bg-amber-500"></div>
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="p-6 bg-white dark:bg-gray-900 text-left">
                                <div class="flex justify-between items-center mb-4">
                                    <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/4"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-gray-800 rounded w-1/6"></div>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between p-3 border border-gray-100 dark:border-gray-800 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-indigo-100 dark:bg-indigo-900/50 rounded flex items-center justify-center">
                                                <div class="h-2 w-2 bg-indigo-500 rounded-full"></div>
                                            </div>
                                            <div>
                                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-24 mb-2"></div>
                                                <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded w-32"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">Active</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between p-3 border border-gray-100 dark:border-gray-800 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 bg-indigo-100 dark:bg-indigo-900/50 rounded flex items-center justify-center">
                                                <div class="h-2 w-2 bg-indigo-500 rounded-full"></div>
                                            </div>
                                            <div>
                                                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-20 mb-2"></div>
                                                <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded w-28"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Offline</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="border-t border-gray-200 dark:border-gray-800 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>&copy; {{ date('Y') }} Server Sentinel. All rights reserved.</p>
        </footer>
    </body>
</html>
