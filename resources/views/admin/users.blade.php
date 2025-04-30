@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-100">Gerenciar Clientes</h1>
        {{-- Botão para Adicionar Cliente (se aplicável) --}}
        {{-- <x-button.primary href="#">Adicionar Cliente</x-button.primary> --}}
    </div>

    {{-- Filtros e Busca --}}
    <x-card class="mb-6">
        <div class="p-4">
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <x-input.text name="search" placeholder="Buscar por nome ou email..." value="{{ request('search') }}" class="block w-full"/>
                    <x-input.select name="status" class="block w-full">
                        <option value="">Todos Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Ativo</option>
                        <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                        <option value="pending_kyc" {{ request('status') == 'pending_kyc' ? 'selected' : '' }}>KYC Pendente</option>
                    </x-input.select>
                     <x-button.secondary type="submit">Filtrar</x-button.secondary>
                </div>
            </form>
        </div>
    </x-card>

    {{-- Tabela de Clientes --}}
    <x-card>
        <x-table :headers="['ID', 'Nome', 'Email', 'Status', 'GoldStay', 'Data Cadastro', 'Ações']">
             @forelse ($users as $user)
                <tr class="hover:bg-gray-700" x-data="{ confirmDelete: false }">
                    <x-table.td>{{ $user->id }}</x-table.td>
                    <x-table.td>{{ $user->name }}</x-table.td>
                    <x-table.td>{{ $user->email }}</x-table.td>
                    <x-table.td>
                        <x-badge :color="$user->status === 'active' ? 'green' : ($user->status === 'pending_kyc' ? 'yellow' : 'red')">
                            {{ ucfirst(str_replace('_', ' ', $user->status)) }}
                        </x-badge>
                    </x-table.td>
                     <x-table.td>{{ number_format($user->goldStayWallet->balance ?? 0, 8) }} GST</x-table.td>
                     <x-table.td>{{ $user->created_at->format('d/m/Y') }}</x-table.td>
                    <x-table.td>
                        <div class="flex space-x-2">
                            <x-button.icon-link href="{{ route('admin.users.edit', $user) }}" title="Editar/Ver Detalhes">
                                <x-heroicon-o-pencil-alt class="w-4 h-4"/>
                            </x-button.icon-link>

                            {{-- Alpine.js para toggle Block/Unblock --}}
                            <form x-data="{ isBlocked: {{ $user->status === 'blocked' ? 'true' : 'false' }} }"
                                  @submit.prevent="
                                      fetch('{{ route('admin.users.toggleBlock', $user) }}', {
                                          method: 'POST',
                                          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                                          body: JSON.stringify({})
                                      })
                                      .then(res => res.json())
                                      .then(data => { if(data.success) isBlocked = !isBlocked; /* Adicionar feedback */ })
                                      .catch(() => {/* Handle error */});
                                  "
                                  class="inline-block">
                                <button type="submit" class="text-yellow-400 hover:text-yellow-300" title="Bloquear/Desbloquear">
                                     <template x-if="!isBlocked"><x-heroicon-o-lock-closed class="w-4 h-4"/></template>
                                     <template x-if="isBlocked"><x-heroicon-o-lock-open class="w-4 h-4"/></template>
                                </button>
                            </form>

                             {{-- Alpine.js para confirmação de Exclusão --}}
                             <div class="inline-block">
                                 <button @click="confirmDelete = true" class="text-red-500 hover:text-red-400" title="Excluir">
                                     <x-heroicon-o-trash class="w-4 h-4"/>
                                 </button>

                                 {{-- Modal de Confirmação (Componente <x-modal.confirm>) --}}
                                 <x-modal.confirm show="confirmDelete" @close="confirmDelete = false" title="Confirmar Exclusão" message="Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.">
                                     <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                         @csrf
                                         @method('DELETE')
                                         <x-button.danger type="submit">Excluir Usuário</x-button.danger>
                                     </form>
                                 </x-modal.confirm>
                            </div>
                        </div>
                    </x-table.td>
                </tr>
            @empty
                 <tr>
                    <x-table.td colspan="7" class="text-center">Nenhum cliente encontrado.</x-table.td>
                </tr>
            @endforelse
        </x-table>

         {{-- Paginação --}}
        <div class="p-4 bg-gray-800 border-t border-gray-700">
            {{ $users->links() }} {{-- Certifique-se que a paginação está estilizada para Tailwind --}}
        </div>
    </x-card>

@endsection

{{-- Placeholders para Componentes (Exemplo) --}}
{{-- resources/views/components/input/text.blade.php --}}
{{-- <input type="text" {{ $attributes->merge(['class' => 'block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md shadow-sm text-sm text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500']) }}> --}}

{{-- resources/views/components/input/select.blade.php --}}
{{-- <select {{ $attributes->merge(['class' => 'block w-full pl-3 pr-10 py-2 bg-gray-700 border border-gray-600 rounded-md shadow-sm text-sm text-gray-200 focus:outline-none focus:ring-amber-500 focus:border-amber-500']) }}>
    {{ $slot }}
</select> --}}

{{-- resources/views/components/button/primary.blade.php --}}
{{-- <a {{ $attributes->merge(['class' => 'inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-500 active:bg-amber-700 focus:outline-none focus:border-amber-700 focus:ring ring-amber-300 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</a> --}} {{-- Se for link, usar <a>, se for button, usar <button> --}}

{{-- resources/views/components/button/secondary.blade.php --}}
{{-- <button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> --}}

{{-- resources/views/components/button/icon-link.blade.php --}}
{{-- <a {{ $attributes->merge(['class' => 'p-1 text-gray-400 hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-amber-500 rounded-full']) }}>
    {{ $slot }}
</a> --}}

{{-- resources/views/components/modal/confirm.blade.php (Alpine.js) --}}
{{-- @props(['show' => false, 'title', 'message'])
<div
    x-show="{{ $show }}"
    style="display: none;"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title" role="dialog" aria-modal="true"
>
    <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        {{-- Overlay --}}
        {{-- <div x-show="{{ $show }}" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="$dispatch('close')" class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" aria-hidden="true"></div>

        {{-- Conteúdo --}}
        {{-- <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            x-show="{{ $show }}"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-bottom transition-all transform bg-gray-800 rounded-lg shadow-xl sm:align-middle"
        >
            <div class="sm:flex sm:items-start">
                <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-900 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                    <x-heroicon-o-exclamation class="w-6 h-6 text-red-400" />
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg font-medium leading-6 text-gray-100" id="modal-title">
                        {{ $title }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-400">
                            {{ $message }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                {{ $slot }} {{-- Botões de ação (Excluir, Cancelar) --}}
                {{-- <x-button.secondary @click="$dispatch('close')" class="ml-3">Cancelar</x-button.secondary> --}}
            {{-- </div>
        </div>
    </div>
</div> --}}