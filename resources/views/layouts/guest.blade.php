<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Libertom') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-gray-300 bg-gray-900">
        <div class="flex flex-col items-center min-h-screen pt-6 sm:justify-center sm:pt-0">
            <div>
                <a href="/">
                    {{-- Libertom Logo --}}
                    <h1 class="text-4xl font-bold text-amber-400">Libertom</h1>
                </a>
            </div>

            <div class="w-full px-6 py-8 mt-6 overflow-hidden bg-gray-800 shadow-md sm:max-w-md rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>