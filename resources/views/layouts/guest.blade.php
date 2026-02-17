<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta name="theme-color" content="#4f46e5">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EPT - English Proficiency Test') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 px-4">
            <div class="mb-4">
                <a href="/">
                    <x-application-logo class="w-16 h-16 sm:w-20 sm:h-20 fill-current text-indigo-600" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-2 sm:mt-6 px-4 py-4 sm:px-6 sm:py-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
