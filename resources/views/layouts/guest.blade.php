<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="text-gray-900 antialiased selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50 dark:bg-gray-900 relative overflow-hidden">
            <!-- Background gradients -->
            <div class="absolute top-0 inset-x-0 h-64 bg-linear-to-b from-indigo-50/50 to-transparent dark:from-indigo-900/20 dark:to-transparent z-0"></div>

            <div class="relative z-10 flex flex-col items-center">
                <a href="/" class="flex items-center gap-2 mb-6">
                    <x-application-logo class="w-12 h-12 text-indigo-600 dark:text-indigo-400" />
                    <span class="font-bold text-2xl tracking-tight text-gray-900 dark:text-white">Server Sentinel</span>
                </a>
            </div>

            <div class="relative z-10 w-full sm:max-w-md mt-2 px-6 py-8 bg-white dark:bg-gray-800 shadow-xl ring-1 ring-gray-900/5 dark:ring-white/10 sm:rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
