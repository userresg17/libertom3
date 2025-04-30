<x-app-layout>
    <x-slot name="header">
        {{ __('Minhas Contas') }}
    </x-slot>

    <div class="space-y-6">
        {{-- Card Resumo e Ações Principais --}}
        <x-card class="bg-gradient-to-r from-gray-800 to-gray-850 border-amber-500/30">
             <div class="p-6">
                 <div class="flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                     {{-- Saldo Total Estimado Fiat --}}
                     <div>
                         <h2 class="text-lg font-semibold text-gray-100">Saldo Fiat Total Estimado</h2>
                         {{-- O saldo total fiat precisará ser calculado no backend --}}
                         <p class="mt-1 text-3xl font-bold text-amber-400">$ {{ number_format($totalFiatBalanceInUSD ?? 0, 2) }} <span class="text-base font-normal text-gray-500">USD</span></p>
                         <p class="text-sm text-gray-400">Valor estimado de todas as moedas fiduciárias</p>
                     </div>
                     {{-- Ações Rápidas Principais --}}
                     <div class="flex flex-wrap justify-start flex-shrink-0 gap-3 md:justify-end">
                         <x-button.primary href="{{ route('wallets.deposit') }}"> {{-- Rota de exemplo --}}
                             <x-heroicon-o-plus-circle class="w-5 h-5 mr-1"/> Depositar
                         </x-button.primary>
                         <x-button.secondary href="{{ route('wallets.withdraw') }}"> {{-- Rota de exemplo --}}
                             <x-heroicon-o-minus-circle class="w-5 h-5 mr-1"/> Sacar
                         </x-button.secondary>
                         <x-button.secondary href="{{ route('wallets.convert') }}"> {{-- Rota de exemplo --}}
                            <x-heroicon-o-refresh class="w-5 h-5 mr-1"/> Converter
                         </x-button.secondary>
                          <x-button.secondary href="{{ route('sendmoney.index') }}">
                             <x-heroicon-o-switch-horizontal class="w-5 h-5 mr-1"/> Enviar
                         </x-button.secondary>
                     </div>
                 </div>

                 {{-- Informação IBAN --}}
                 <div class="pt-4 mt-4 border-t border-gray-700/50" x-data="{ copied: false }">
                    <h3 class="text-sm font-medium text-gray-400">Seu IBAN Global Unificado</h3>
                    <div class="flex items-center justify-between mt-1">
                        <p class="font-mono text-lg text-gray-100 break-all">{{ $user->iban ?? 'IBAN Indisponível' }}</p>
                        <button @click="navigator.clipboard.writeText('{{ $user->iban ?? '' }}'); copied = true; setTimeout(() => copied = false, 1500)"
                                class="p-1.5 text-gray-400 rounded hover:bg-gray-700 hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:ring-offset-gray-800"
                                title="Copiar IBAN">
                            <span x-show="!copied"><x-heroicon-o-clipboard-copy class="w-5 h-5"/></span>
                            <span x-show="copied" class="text-green-400"><x-heroicon-o-check class="w-5 h-5"/></span>
                        </button>
                    </div>
                     <p class="text-xs text-gray-500">Use este IBAN para receber transferências internacionais diretamente em sua conta Libertom.</p>
                 </div>
             </div>
        </x-card>

        {{-- Lista de Carteiras por Moeda --}}
        <div>
             <h2 class="mb-4 text-xl font-semibold text-gray-100">Saldos por Moeda</h2>
             <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
                 {{-- Loop através das carteiras fiat do usuário ($fiatWallets é uma coleção vinda do Controller) --}}
                 @forelse ($fiatWallets as $wallet)
                    <x-card class="relative overflow-hidden">
                         <div class="p-5">
                             {{-- Header da Carteira --}}
                             <div class="flex items-center justify-between mb-3">
                                 <div class="flex items-center space-x-3">
                                     {{-- Placeholder para Flag da Moeda (usar uma lib ou mapeamento) --}}
                                     <span class="inline-block w-8 h-6 bg-gray-600 rounded"></span>
                                     <span class="text-lg font-semibold text-white">{{ $wallet->currency }}</span>
                                 </div>
                                 {{-- Menu Dropdown de Ações (opcional) --}}
                                 <div x-data="{ open: false }" class="relative">
                                      <button @click="open = !open" class="p-1 text-gray-400 rounded-full hover:bg-gray-700 hover:text-white">
                                           <x-heroicon-s-dots-vertical class="w-5 h-5"/>
                                      </button>
                                      <div x-show="open" @click.away="open = false" style="display: none;" x-cloak
                                           class="absolute right-0 z-10 w-48 mt-1 origin-top-right bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                          <div class="py-1">
                                              <a href="{{ route('wallets.deposit', ['currency' => $wallet->currency]) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600 hover:text-amber-400">Depositar</a>
                                              <a href="{{ route('wallets.withdraw', ['currency' => $wallet->currency]) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600 hover:text-amber-400">Sacar</a>
                                              <a href="{{ route('wallets.convert', ['from' => $wallet->currency]) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600 hover:text-amber-400">Converter</a>
                                              <a href="{{ route('transactions.history', ['currency' => $wallet->currency]) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-600 hover:text-amber-400">Extrato</a>
                                          </div>
                                      </div>
                                 </div>
                             </div>

                             {{-- Saldo --}}
                              <p class="text-3xl font-bold text-white">
                                {{ number_format($wallet->balance, $wallet->decimal_places ?? 2) }} {{-- Ajustar casas decimais se necessário (JPY etc) --}}
                             </p>
                              {{-- Valor Equivalente (opcional) --}}
                             <p class="text-sm text-gray-400">~ ${{ number_format($wallet->balance_in_usd ?? 0, 2) }} USD</p>

                         </div>
                         {{-- Mini-gráfico de variação (opcional, placeholder) --}}
                         {{-- <div class="absolute bottom-0 left-0 w-full h-10 opacity-20 bg-gradient-to-t from-amber-500/50 to-transparent"></div> --}}
                    </x-card>
                 @empty
                    <p class="text-center text-gray-500 md:col-span-2 lg:col-span-3">Nenhuma carteira fiat ativa encontrada.</p>
                 @endforelse

                  {{-- Card para Adicionar Nova Moeda (se aplicável) --}}
                  {{-- <button class="flex flex-col items-center justify-center h-full p-5 text-center border-2 border-gray-700 border-dashed rounded-lg hover:border-amber-500 hover:bg-gray-800 text-gray-400 hover:text-amber-400 transition duration-150 ease-in-out">
                      <x-heroicon-o-plus class="w-8 h-8 mb-2"/>
                      <span class="text-sm font-medium">Adicionar Moeda</span>
                  </button> --}}
             </div>
        </div>

        {{-- Link para Histórico Geral de Transações --}}
        <div class="mt-8 text-center">
            <a href="{{ route('transactions.history') }}" class="text-sm text-amber-400 hover:underline">
                Ver histórico completo de transações
            </a>
        </div>
    </div>
</x-app-layout>