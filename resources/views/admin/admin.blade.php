<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark"> {{-- Forçar Dark Mode como padrão --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Libertom') }} - Admin Panel</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    {{-- Usando Inter como exemplo, pode ser Poppins ou outra --}}
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Estilos para scrollbar (opcional, para consistência visual) */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1f2937; /* gray-800 */
        }
        ::-webkit-scrollbar-thumb {
            background: #4b5563; /* gray-600 */
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #6b7280; /* gray-500 */
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-300">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        <aside
            class="fixed inset-y-0 left-0 z-30 flex flex-col flex-shrink-0 w-64 transition-transform duration-300 ease-in-out transform bg-gray-800 border-r border-gray-700 lg:static lg:translate-x-0"
            :class="{'-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen}"
            @click.away="sidebarOpen = false"
        >
            <div class="flex items-center justify-center h-16 px-4 bg-gray-900">
                <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-amber-400">
                    Libertom <span class="text-gray-400 text-sm">Admin</span>
                </a>
            </div>

            <nav class="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    <x-heroicon-o-home class="w-5 h-5 mr-2"/> Dashboard
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    <x-heroicon-o-users class="w-5 h-5 mr-2"/> Clientes
                </x-admin.nav-link>
                 <x-admin.nav-link :href="route('admin.investments.index')" :active="request()->routeIs('admin.investments.*')">
                    <x-heroicon-o-chart-bar class="w-5 h-5 mr-2"/> Investimentos
                </x-admin.nav-link>
                 <x-admin.nav-link :href="route('admin.transactions.index')" :active="request()->routeIs('admin.transactions.*')">
                    <x-heroicon-o-clipboard-list class="w-5 h-5 mr-2"/> Transações
                </x-admin.nav-link>
                {{-- Adicionar link para Gestão de Funcionários aqui quando a rota existir --}}
                {{-- <x-admin.nav-link :href="route('admin.employees.index')" :active="request()->routeIs('admin.employees.*')">
                    <x-heroicon-o-briefcase class="w-5 h-5 mr-2"/> Funcionários
                </x-admin.nav-link> --}}
                 <x-admin.nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')">
                    <x-heroicon-o-cog class="w-5 h-5 mr-2"/> Configurações
                </x-admin.nav-link>
            </nav>
        </aside>

        <div class="flex flex-col flex-1 overflow-hidden">
            <header class="relative z-10 flex items-center justify-between h-16 px-4 bg-gray-800 border-b border-gray-700 lg:px-6">
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 lg:hidden hover:text-gray-200 focus:outline-none focus:ring">
                     <span class="sr-only">Open sidebar</span>
                     <x-heroicon-o-menu class="w-6 h-6"/>
                </button>

                <div class="flex-1 hidden lg:block ml-4">
                    {{-- @yield('breadcrumbs') --}}
                    {{-- Exemplo: <span class="text-sm text-gray-500">Dashboard / Clientes</span> --}}
                </div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center space-x-2 text-sm text-gray-400 hover:text-gray-200 focus:outline-none">
                        <span>{{ Auth::user()->name ?? 'Admin' }}</span>
                        <x-heroicon-s-chevron-down class="w-4 h-4"/>
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
                        style="display: none;" {{-- Evita flash no load --}}
                    >
                        <div class="py-1">
                            {{-- <a href="#" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600">Meu Perfil</a> --}}
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

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>