@props(['title' => null, 'breadcrumbs' => []])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ ($title ? $title . ' | ' : '') . config('app.name', 'WISARY Laundry') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('images/wisary-logo-mark.svg') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full bg-primary-50 text-neutral-950 antialiased">
        <div class="min-h-full flex flex-col lg:flex-row">

            {{-- Desktop Sidebar --}}
            <aside class="hidden lg:flex lg:flex-col lg:w-[248px] lg:shrink-0 bg-primary-800 text-white">
                <div class="px-3 py-4 border-b border-primary-700/40">
                    <a href="{{ route('dashboard') }}" class="block">
                        <img src="{{ asset('images/wisary-logo-sidebar.svg') }}" alt="WISARY Laundry" class="w-full">
                    </a>
                </div>
                <div class="flex flex-col flex-1 overflow-y-auto">
                    <x-partials.nav-items />
                </div>
                <div class="p-3 border-t border-primary-700/40 space-y-2">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 min-h-12 rounded-lg transition-colors hover:bg-primary-700/40">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary-600 text-white text-sm font-semibold" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs {{ auth()->user()->isSuperAdmin() ? 'text-primary-200' : 'text-primary-300' }}">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Staf' }}</p>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 min-h-12 text-sm font-medium text-neutral-100 hover:bg-primary-700/40 hover:text-white rounded-lg transition-colors border-l-4 border-transparent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                            </svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Mobile Drawer --}}
            <div data-drawer class="fixed inset-0 z-50 hidden lg:hidden" role="dialog" aria-modal="true" aria-label="Menu navigasi">
                <div data-drawer-backdrop class="fixed inset-0 bg-neutral-950/50"></div>
                <div class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-primary-800 text-white flex flex-col transition-transform duration-200">
                    <div class="flex items-center justify-between px-5 py-5 border-b border-primary-700/40">
                        <a href="{{ route('dashboard') }}" class="block w-auto shrink-0">
                            <img src="{{ asset('images/wisary-logo-sidebar.svg') }}" alt="WISARY Laundry" class="h-11 w-auto">
                        </a>
                        <button type="button" data-drawer-close class="inline-flex items-center justify-center w-12 h-12 text-white hover:bg-primary-700/40 rounded-lg" aria-label="Tutup menu">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-3 border-b border-primary-700/40 space-y-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 min-h-12 rounded-lg transition-colors hover:bg-primary-700/40">
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-primary-600 text-white text-sm font-semibold" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-primary-300">{{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Staf' }}</p>
                            </div>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center gap-3 px-4 py-3 min-h-12 text-sm font-medium text-neutral-100 hover:bg-primary-700/40 hover:text-white rounded-lg transition-colors border-l-4 border-transparent">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                    <div class="flex flex-col flex-1 overflow-y-auto">
                        <x-partials.nav-items mobile />
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="flex-1 flex flex-col min-w-0">
                {{-- Top Bar (mobile) --}}
                <header class="lg:hidden sticky top-0 z-40 bg-primary-800 text-white">
                    <div class="flex items-center justify-between px-4 h-16">
<a href="{{ route('dashboard') }}" class="block w-auto shrink-0">
                        <img src="{{ asset('images/wisary-logo-sidebar.svg') }}" alt="WISARY Laundry" class="h-10 w-auto">
                    </a>
                        <button type="button" data-drawer-open class="inline-flex items-center justify-center w-12 h-12 text-white hover:bg-primary-700/40 rounded-lg" aria-label="Buka menu" aria-haspopup="true">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </header>

                {{-- Page Header (desktop) --}}
                <div class="hidden lg:block border-b border-neutral-100 bg-white">
                    <div class="px-8 py-4 flex items-center justify-between">
                        @if (! empty($breadcrumbs))
                            <nav class="text-sm text-neutral-500" aria-label="Breadcrumb">
                                @foreach ($breadcrumbs as $index => $crumb)
                                    @if ($index > 0)
                                        <span class="mx-1 text-neutral-300">/</span>
                                    @endif
                                    @if (isset($crumb['url']))
                                        <a href="{{ $crumb['url'] }}" class="hover:text-primary-700">{{ $crumb['label'] }}</a>
                                    @else
                                        <span class="text-neutral-950 font-medium">{{ $crumb['label'] }}</span>
                                    @endif
                                @endforeach
                            </nav>
                        @else
                            <span></span>
                        @endif

                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 min-h-12 lg:min-h-10 text-sm font-medium text-neutral-700 hover:text-primary-700 hover:bg-primary-50 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                                {{ auth()->user()->name }}
                            </a>
                            {{ $headerActions ?? '' }}
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <main class="flex-1 px-4 py-6 lg:px-8 lg:py-8 w-full max-w-7xl mx-auto">
                    <div class="lg:hidden mb-4 flex flex-col gap-3">
                        <h1 class="text-2xl font-bold text-primary-900">{{ $title }}</h1>
                        @isset($headerActions)
                            <div class="flex items-center gap-2">{{ $headerActions }}</div>
                        @endisset
                    </div>

                    {{ $slot }}
                </main>

                {{-- Mobile Bottom Navigation --}}
                <nav class="lg:hidden sticky bottom-0 z-30 bg-white border-t border-neutral-100 shadow-lg" aria-label="Navigasi bawah">
                    @php
                        $bottom = [
                            ['route' => 'dashboard', 'label' => 'Dasbor', 'icon' => 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
                            ['route' => 'orders', 'label' => 'Pesanan', 'icon' => 'M4 6h16M4 12h16M4 18h16'],
                            ['route' => 'orders.create', 'label' => 'Buat', 'icon' => 'M12 5v14M5 12h14'],
                            ['route' => 'customers', 'label' => 'Pelanggan', 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z'],
                            ['route' => 'services', 'label' => 'Layanan', 'icon' => 'M3 6h18M3 12h18M3 18h18'],
                        ];
                    @endphp
                    <ul class="grid grid-cols-5">
                        @foreach ($bottom as $item)
                            @if ($item['route'] === 'orders.create')
                                <li>
                                    <a href="{{ route($item['route']) }}" class="flex flex-col items-center justify-center min-h-14 py-1" aria-label="Buat pesanan">
                                        <span class="flex items-center justify-center w-14 h-14 -mt-6 rounded-full bg-primary-700 text-white shadow-lg ring-4 ring-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M12 5v14M5 12h14"/>
                                            </svg>
                                        </span>
                                        <span class="mt-1 text-[11px] font-medium {{ request()->routeIs($item['route']) ? 'text-primary-700' : 'text-neutral-500' }}">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a href="{{ route($item['route']) }}" class="flex flex-col items-center justify-center gap-1 py-2 min-h-14 text-[11px] font-medium {{ request()->routeIs($item['route']) ? 'text-primary-700' : 'text-neutral-500' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="{{ $item['icon'] }}"/>
                                        </svg>
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>

        {{-- Toasts (from session flash) --}}
        @if (session('success'))
            <x-toast type="success" data-toast-show data-toast-auto>✓ {{ session('success') }}</x-toast>
        @endif
        @if (session('error'))
            <x-toast type="error" data-toast-show data-toast-auto>✕ {{ session('error') }}</x-toast>
        @endif
        @if (session('status'))
            <x-toast type="success" data-toast-show data-toast-auto>✓ {{ ['profile-updated' => 'Profil berhasil diperbarui.', 'password-updated' => 'Kata sandi berhasil diubah.'][session('status')] ?? session('status') }}</x-toast>
        @endif

        @livewireScripts
    </body>
</html>