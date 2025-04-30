@extends('layouts.admin')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-gray-100">Extrato Geral de Transações</h1>

    {{-- Filtros --}}
    <x-card class="mb-6">
         <form method="GET" action="{{ route('admin.transactions.index') }}" class="p-4">
             <div class="grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-5">
                 <x-input.text name="user_search" placeholder="Buscar Cliente (Nome/Email)" value="{{ request('user_search') }}" />
                 <x-input.select name="type">
                     <option value="">Todos Tipos</option>
                     {{-- Popular com tipos: deposit, withdrawal, transfer, investment_buy, investment_sell, fee, adjustment, etc. --}}
                     <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Depósito</option>
                     <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Saque</option>
                      <option value="investment_buy" {{ request('type') == 'investment_buy' ? 'selected' : '' }}>Compra Invest.</option>
                     <option value="investment_sell" {{ request('type') == 'investment_sell' ? 'selected' : '' }}>Venda Invest.</option>
                     <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transferência</option>
                 </x-input.select>
                  <x-input.select name="status">
                     <option value="">Todos Status</option>
                     <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                     <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluída</option>
                     <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falhou</option>
                     <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelada</option>
                 </x-input.select>
                 <x-input.text type="date" name="date_from" value="{{ request('date_from') }}" />
                 <x-input.text type="date" name="date_to" value="{{ request('date_to') }}" />
             </div>
             <div class="mt-4 text-right">
                 <x-button.secondary type="submit">Filtrar Transações</x-button.secondary>
             </div>
         </form>
    </x-card>

    {{-- Tabela de Transações --}}
    <x-card>
         <x-table :headers="['ID', 'Data', 'Cliente', 'Tipo', 'Descrição/Ativo', 'Valor', 'Status', 'Ações']">
             @forelse ($transactions as $tx)
                <tr class="hover:bg-gray-700">
                    <x-table.td>{{ $tx->id }}</x-table.td>
                    <x-table.td>{{ $tx->created_at->format('d/m/Y H:i') }}</x-table.td>
                    <x-table.td>
                        <a href="{{ route('admin.users.edit', $tx->user) }}" class="text-amber-400 hover:underline">
                           {{ $tx->user->name ?? 'N/A' }} ({{ $tx->user->id }})
                        </a>
                    </x-table.td>
                    <x-table.td>{{ ucfirst(str_replace('_', ' ', $tx->type)) }}</x-table.td>
                    <x-table.td>{{ $tx->description ?? $tx->asset?->name ?? 'N/A' }}</x-table.td>
                    <x-table.td>
                         @if ($tx->currency)
                            <span class="{{ $tx->amount < 0 ? 'text-red-400' : 'text-green-400' }}">
                                {{ $tx->currency }} {{ number_format($tx->amount, 2) }}
                            </span>
                         @elseif ($tx->asset)
                             {{-- Lógica para valor de investimento --}}
                             <span class="{{ $tx->type == 'investment_sell' ? 'text-green-400' : 'text-red-400' }}">
                                 {{ number_format($tx->quantity, 4) }} {{ $tx->asset->symbol }} @ {{ number_format($tx->price_per_unit, 2) }}
                             </span>
                         @endif
                    </x-table.td>
                    <x-table.td>
                        <x-badge :color="$tx->status == 'completed' ? 'green' : ($tx->status == 'pending' ? 'yellow' : 'red')">
                             {{ ucfirst($tx->status) }}
                        </x-badge>
                    </x-table.td>
                    <x-table.td>
                        {{-- Ações: Ver detalhes, Aprovar/Rejeitar Saque Pendente --}}
                        @if($tx->type === 'withdrawal' && $tx->status === 'pending')
                             <div class="flex space-x-1" x-data>
                                 <form @submit.prevent="fetch('{{ route('admin.transactions.approveWithdrawal', $tx) }}', {method:'POST', headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(() => location.reload())">
                                     <x-button.icon type="submit" class="text-green-400 hover:text-green-300" title="Aprovar Saque">
                                         <x-heroicon-o-check-circle class="w-4 h-4"/>
                                     </x-button.icon>
                                 </form>
                                 <form @submit.prevent="fetch('{{ route('admin.transactions.rejectWithdrawal', $tx) }}', {method:'POST', headers: {'X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(() => location.reload())">
                                      <x-button.icon type="submit" class="text-red-500 hover:text-red-400" title="Rejeitar Saque">
                                          <x-heroicon-o-x-circle class="w-4 h-4"/>
                                      </x-button.icon>
                                 </form>
                             </div>
                         @else
                            {{-- <x-button.icon-link href="#" title="Ver Detalhes">
                                <x-heroicon-o-eye class="w-4 h-4"/>
                            </x-button.icon-link> --}}
                         @endif
                    </x-table.td>
                </tr>
             @empty
                 <tr>
                    <x-table.td colspan="8" class="text-center">Nenhuma transação encontrada com os filtros aplicados.</x-table.td>
                 </tr>
             @endforelse
         </x-table>

         {{-- Paginação --}}
         <div class="p-4 bg-gray-800 border-t border-gray-700">
             {{ $transactions->appends(request()->query())->links() }}
         </div>
    </x-card>

@endsection

{{-- Placeholder para Componentes (Exemplo) --}}
{{-- resources/views/components/button/icon.blade.php --}}
{{-- <button {{ $attributes->merge(['type' => 'button', 'class' => 'p-1 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-amber-500 rounded-full']) }}>
    {{ $slot }}
</button> --}}