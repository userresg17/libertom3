@extends('layouts.admin')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-gray-100">Dashboard</h1>

    {{-- KPIs Grid --}}
    <div class="grid grid-cols-1 gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Exemplo de KPI Card (Componente <x-card.kpi>) --}}
        <x-card.kpi title="Total de Clientes" value="{{ $kpis['total_users'] ?? 'N/A' }}" icon="heroicon-o-users" color="blue"/>
        <x-card.kpi title="Saldo Total (USD)" value="$ {{ number_format($kpis['total_balance_usd'] ?? 0, 2) }}" icon="heroicon-o-currency-dollar" color="green"/>
        <x-card.kpi title="Volume Investimentos (USD)" value="$ {{ number_format($kpis['total_investments_usd'] ?? 0, 2) }}" icon="heroicon-o-chart-bar" color="amber"/>
        <x-card.kpi title="Transações (24h)" value="{{ $kpis['transactions_24h'] ?? 'N/A' }}" icon="heroicon-o-switch-horizontal" color="indigo"/>
        {{-- Adicionar mais KPIs conforme necessário (ex: GoldStay total, Saques pendentes) --}}
    </div>

    {{-- Recent Transactions Table --}}
    <h2 class="mb-4 text-xl font-semibold text-gray-100">Transações Recentes</h2>
    <x-card>
        <x-table :headers="['Data', 'Cliente', 'Tipo', 'Valor', 'Status']">
            @forelse ($recentTransactions as $tx)
                <tr class="hover:bg-gray-700">
                    <x-table.td>{{ $tx->created_at->format('d/m/Y H:i') }}</x-table.td>
                    <x-table.td>{{ $tx->user->name ?? 'N/A' }}</x-table.td>
                    <x-table.td>{{ ucfirst($tx->type) }}</x-table.td>
                    <x-table.td>
                        {{ $tx->currency }} {{ number_format($tx->amount, 2) }}
                        @if($tx->asset) ({{ $tx->asset->symbol }}) @endif
                    </x-table.td>
                    <x-table.td>
                        <x-badge :color="$tx->status == 'completed' ? 'green' : ($tx->status == 'pending' ? 'yellow' : 'red')">
                            {{ ucfirst($tx->status) }}
                        </x-badge>
                    </x-table.td>
                </tr>
            @empty
                <tr>
                    <x-table.td colspan="5" class="text-center">Nenhuma transação recente encontrada.</x-table.td>
                </tr>
            @endforelse
        </x-table>
        {{-- Adicionar links de paginação se houver muitas transações --}}
        {{-- <div class="mt-4"> {{ $recentTransactions->links() }} </div> --}}
    </x-card>

@endsection

{{-- Placeholders para Componentes (exemplo) --}}
{{-- resources/views/components/card/kpi.blade.php (exemplo simples) --}}
{{-- @props(['title', 'value', 'icon', 'color' => 'gray'])
<div class="p-4 bg-gray-800 border border-gray-700 rounded-lg shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-gray-400">{{ $title }}</p>
            <p class="text-2xl font-semibold text-gray-100">{{ $value }}</p>
        </div>
        <div class="p-2 bg-{{ $color }}-600 bg-opacity-20 text-{{ $color }}-400 rounded-full">
             <x-dynamic-component :component="'heroicon-o-'. $icon" class="w-6 h-6"/>
        </div>
    </div>
</div> --}}

{{-- resources/views/components/card.blade.php --}}
{{-- <div {{ $attributes->merge(['class' => 'bg-gray-800 border border-gray-700 rounded-lg shadow overflow-hidden']) }}>
    {{ $slot }}
</div> --}}

{{-- resources/views/components/table.blade.php --}}
{{-- @props(['headers'])
<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-700">
    <thead class="bg-gray-750"> {{-- Slightly different shade for header --}}
        {{-- <tr>
            @foreach ($headers as $header)
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                {{ $header }}
            </th>
            @endforeach
        </tr>
    </thead>
    <tbody class="bg-gray-800 divide-y divide-gray-700">
        {{ $slot }}
    </tbody>
</table>
</div> --}}

{{-- resources/views/components/table/td.blade.php --}}
{{-- <td {{ $attributes->merge(['class' => 'px-6 py-4 whitespace-nowrap text-sm text-gray-300']) }}>
    {{ $slot }}
</td> --}}

{{-- resources/views/components/badge.blade.php --}}
{{-- @props(['color' => 'gray'])
@php
$colors = [
    'gray' => 'bg-gray-700 text-gray-300',
    'red' => 'bg-red-800 text-red-300',
    'yellow' => 'bg-yellow-800 text-yellow-300',
    'green' => 'bg-green-800 text-green-300',
    'blue' => 'bg-blue-800 text-blue-300',
    'indigo' => 'bg-indigo-800 text-indigo-300',
    'purple' => 'bg-purple-800 text-purple-300',
    'pink' => 'bg-pink-800 text-pink-300',
    'amber' => 'bg-amber-800 text-amber-300', // Dourado/Ambar
];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$color] ?? $colors['gray'] }}">
    {{ $slot }}
</span> --}}