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
    @stack('styles') {{-- Placeholder for page-specific styles --}}

    <style>
        /* Scrollbar styles (optional) */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #1f2937; /* gray-800 */ }
        ::-webkit-scrollbar-thumb { background: #4b5563; /* gray-600 */ border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; /* gray-500 */ }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-300">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col flex-shrink-0 w-64 transition-transform duration-300 ease-in-out transform bg-gray-800 border-r border-gray-700 lg:static lg:translate-x-0"
            :class="{'-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen}"
            @click.away="sidebarOpen = false"
            aria-label="Sidebar"
        >
            <div class="flex items-center justify-center h-16 px-4 bg-gray-900">
                <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-amber-400">
                    Libertom
                </a>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                {{-- Use a Blade component for nav links for cleaner code --}}
                <x-app.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    <x-heroicon-o-home class="w-5 h-5 mr-3"/> Dashboard
                </x-app.nav-link>
                <x-app.nav-link :href="route('wallets.index')" :active="request()->routeIs('wallets.*')">
                    <x-heroicon-o-collection class="w-5 h-5 mr-3"/> Contas
                </x-app.nav-link>
                 <x-app.nav-link :href="route('investments.index')" :active="request()->routeIs('investments.*')">
                    <x-heroicon-o-chart-bar class="w-5 h-5 mr-3"/> Investimentos
                </x-app.nav-link>
                 <x-app.nav-link :href="route('goldstay.index')" :active="request()->routeIs('goldstay.*')">
                    {{-- Assuming you have a GoldStay icon or use a generic one --}}
                    <x-heroicon-o-cube class="w-5 h-5 mr-3"/> GoldStay [cite: 3]
                </x-app.nav-link>
                <x-app.nav-link :href="route('sendmoney.index')" :active="request()->routeIs('sendmoney.*')">
                    <x-heroicon-o-switch-horizontal class="w-5 h-5 mr-3"/> Enviar Dinheiro [cite: 1]
                </x-app.nav-link>
                 <x-app.nav-link :href="route('giftcards.index')" :active="request()->routeIs('giftcards.*')">
                    <x-heroicon-o-gift class="w-5 h-5 mr-3"/> Gift Cards [cite: 1]
                </x-app.nav-link>

                {{-- Separator --}}
                <div class="pt-2 mt-2 border-t border-gray-700"></div>

                <x-app.nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">
                    <x-heroicon-o-user-circle class="w-5 h-5 mr-3"/> Meu Perfil
                </x-app.nav-link>
                {{-- Add other links like Settings, Help, etc. if needed --}}

            </nav>
             <div class="p-4 mt-auto border-t border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-3 py-2 text-sm font-medium text-gray-400 rounded-md hover:bg-gray-700 hover:text-amber-400 group">
                         <x-heroicon-o-logout class="w-5 h-5 mr-3 text-gray-500 group-hover:text-amber-400"/>
                        Sair
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex flex-col flex-1 overflow-hidden">
            <header class="relative z-10 flex items-center justify-between h-16 px-4 bg-gray-800 border-b border-gray-700 lg:px-6">
                 <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 lg:hidden hover:text-gray-200 focus:outline-none focus:ring">
                     <span class="sr-only">Open sidebar</span>
                     <x-heroicon-o-menu class="w-6 h-6"/>
                </button>

                 <div class="flex-1"></div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-400 rounded-full hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-amber-500">
                        {{-- User Avatar Placeholder --}}
                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gray-600 rounded-full">
                            <span class="text-xs font-medium leading-none text-white">{{ Str::substr(Auth::user()->name, 0, 1) }}</span>
                        </span>
                        <span class="hidden lg:inline">{{ Auth::user()->name }}</span>
                        <x-heroicon-s-chevron-down class="hidden w-4 h-4 lg:inline"/>
                    </button>
                    <div
                        x-show="open"
                        @click.away="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 w-48 mt-2 origin-top-right bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        style="display: none;"
                        x-cloak
                    >
                        <div class="py-1">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600 hover:text-amber-400">Meu Perfil</a>
                            {{-- Add other dropdown links if needed --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); this.closest('form').submit();"
                                   class="block w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-gray-600">
                                    Sair
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 overflow-x-hidden overflow-y-auto lg:p-6">
                 @if (session('success'))
                    <div class="p-4 mb-4 text-sm text-green-300 bg-green-800 border border-green-700 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                 @endif
                 @if (session('error'))
                     <div class="p-4 mb-4 text-sm text-red-300 bg-red-800 border border-red-700 rounded-lg" role="alert">
                         {{ session('error') }}
                     </div>
                 @endif
                 @if (session('status')) {{-- For things like password reset success --}}
                     <div class="p-4 mb-4 text-sm text-blue-300 bg-blue-800 border border-blue-700 rounded-lg" role="alert">
                         {{ session('status') }}
                     </div>
                 @endif

                {{-- Page Heading --}}
                @if (isset($header))
                    <header class="mb-6">
                        <h1 class="text-2xl font-semibold text-gray-100">
                            {{ $header }}
                        </h1>
                    </header>
                @endif

                 {{-- Main Page Content --}}
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts') {{-- Placeholder for page-specific scripts --}}
</body>
</html>