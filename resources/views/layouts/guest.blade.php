<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'WISARY Laundry') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/wisary-logo-mark.svg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans text-neutral-950 antialiased bg-primary-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 sm:py-12">
            <div class="w-full sm:max-w-md">
                <div class="flex flex-col items-center mb-6">
                    <img src="{{ asset('images/wisary-logo.svg') }}" alt="WISARY Laundry" class="w-64 sm:w-72 max-w-full">
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 px-6 py-8 sm:px-8">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>