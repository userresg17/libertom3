<x-app-layout>
    <x-slot name="header">
        {{ __('Dashboard') }}
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Coluna Principal (Esquerda/Centro) --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Card de Saldo Principal --}}
            <x-card class="bg-gradient-to-r from-gray-800 to-gray-850 border-amber-500/30">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-100">Saldo Total Estimado</h2>
                        {{-- Selector de Moeda (opcional) --}}
                        <x-input.select name="main_currency" class="text-xs !py-1 !px-2 !bg-gray-700">
                            <option value="USD">USD</option>
                            <option value="BRL">BRL</option>
                            <option value="EUR">EUR</option>
                            {{-- Adicionar outras moedas principais --}}
                        </x-input.select>
                    </div>
                    {{-- O saldo total precisará ser calculado no backend --}}
                    <p class="text-4xl font-bold text-amber-400">$ {{ number_format($totalBalanceInUSD ?? 0, 2) }}</p>
                     <p class="text-sm text-gray-400">Valor estimado em USD</p>

                    {{-- Ações Rápidas --}}
                    <div class="flex flex-wrap gap-4 mt-6">
                        <x-button.primary href="{{ route('wallets.deposit') }}"> {{-- Assume uma rota de depósito --}}
                            <x-heroicon-o-plus-circle class="w-5 h-5 mr-1"/> Depositar
                        </x-button.primary>
                        <x-button.secondary href="{{ route('sendmoney.index') }}">
                            <x-heroicon-o-switch-horizontal class="w-5 h-5 mr-1"/> Enviar [cite: 1]
                        </x-button.secondary>
                         <x-button.secondary href="{{ route('wallets.convert') }}"> {{-- Assume uma rota de conversão --}}
                            <x-heroicon-o-refresh class="w-5 h-5 mr-1"/> Converter
                        </x-button.secondary>
                         <x-button.secondary href="{{ route('investments.index') }}">
                            <x-heroicon-o-chart-bar class="w-5 h-5 mr-1"/> Investir [cite: 1]
                        </x-button.secondary>
                    </div>
                </div>
            </x-card>

            {{-- Card de Atividade Recente --}}
            <x-card>
                 <div class="flex items-center justify-between p-4 border-b border-gray-700">
                     <h3 class="font-semibold text-gray-100">Atividade Recente</h3>
                     <a href="{{ route('transactions.history') }}" class="text-sm text-amber-400 hover:underline">Ver Tudo</a> {{-- Assume rota de histórico --}}
                 </div>
                 <div class="divide-y divide-gray-700">
                     {{-- Loop através das transações recentes ($recentActivity) --}}
                     @forelse ($recentActivity as $activity)
                         <div class="flex items-center justify-between p-4 hover:bg-gray-750">
                             <div class="flex items-center space-x-3">
                                 <span class="p-2 bg-gray-700 rounded-full">
                                     {{-- Ícone baseado no tipo de atividade --}}
                                     @if($activity->type == 'deposit') <x-heroicon-o-arrow-down class="w-5 h-5 text-green-400"/>
                                     @elseif($activity->type == 'withdrawal') <x-heroicon-o-arrow-up class="w-5 h-5 text-red-400"/>
                                     @elseif($activity->type == 'investment_buy') <x-heroicon-o-chart-bar class="w-5 h-5 text-blue-400"/>
                                     @elseif($activity->type == 'transfer_out') <x-heroicon-o-minus-circle class="w-5 h-5 text-red-400"/>
                                     @elseif($activity->type == 'goldstay_credit') <x-heroicon-o-cube class="w-5 h-5 text-amber-400"/>
                                     @else <x-heroicon-o-document-text class="w-5 h-5 text-gray-400"/>
                                     @endif
                                 </span>
                                 <div>
                                     <p class="text-sm font-medium text-gray-200">{{ $activity->description ?? 'Descrição da Atividade' }}</p>
                                     <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                                 </div>
                             </div>
                             <div class="text-right">
                                  <p class="text-sm font-medium {{ $activity->amount > 0 ? 'text-green-400' : 'text-red-400' }}">
                                     {{ $activity->amount > 0 ? '+' : '' }}{{ $activity->currency ?? 'GST' }} {{ number_format(abs($activity->amount), $activity->currency ? 2 : 8) }}
                                  </p>
                                  <p class="text-xs text-gray-500">{{ $activity->status ?? 'Completed' }}</p>
                             </div>
                         </div>
                     @empty
                         <p class="p-4 text-sm text-center text-gray-500">Nenhuma atividade recente.</p>
                     @endforelse
                 </div>
            </x-card>
        </div>

        {{-- Coluna Lateral (Direita) --}}
        <div class="space-y-6 lg:col-span-1">
            {{-- Card GoldStay Resumo --}}
            <x-card>
                <div class="p-6">
                    <div class="flex items-center mb-3 space-x-2">
                         <x-heroicon-o-cube class="w-6 h-6 text-amber-400"/>
                         <h3 class="font-semibold text-gray-100">Saldo GoldStay (GST) [cite: 3]</h3>
                    </div>
                     <p class="text-2xl font-bold text-white">{{ number_format($goldStayBalance ?? 0, 8) }} GST</p>
                     {{-- Valor equivalente em fiat --}}
                     <p class="text-sm text-gray-400">~ $ {{ number_format($goldStayValueUSD ?? 0, 2) }} USD</p>
                     <a href="{{ route('goldstay.index') }}" class="inline-block mt-4 text-sm text-amber-400 hover:underline">Gerenciar GoldStay</a>
                </div>
            </x-card>

             {{-- Card de Investimentos Resumo --}}
             <x-card>
                 <div class="p-6">
                     <div class="flex items-center mb-3 space-x-2">
                          <x-heroicon-o-chart-pie class="w-6 h-6 text-blue-400"/>
                          <h3 class="font-semibold text-gray-100">Resumo de Investimentos [cite: 26]</h3>
                     </div>
                      <p class="text-2xl font-bold text-white">$ {{ number_format($totalInvestmentValueUSD ?? 0, 2) }}</p>
                      {{-- Variação do portfólio (exemplo) --}}
                      <p class="text-sm {{ ($portfolioChange24h ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                          {{ ($portfolioChange24h ?? 0) >= 0 ? '+' : '' }}{{ number_format($portfolioChange24h ?? 0, 2) }}% (24h)
                      </p>
                      <a href="{{ route('investments.index') }}" class="inline-block mt-4 text-sm text-amber-400 hover:underline">Ver Portfólio</a>
                 </div>
             </x-card>

             {{-- Card KYC Status / Ações --}}
             @if (Auth::user()->kyc_status !== 'approved')
             <x-card class="border-yellow-500/30">
                  <div class="p-6">
                      <div class="flex items-center mb-3 space-x-2">
                           <x-heroicon-o-exclamation-circle class="w-6 h-6 text-yellow-400"/>
                           <h3 class="font-semibold text-gray-100">Verificação Pendente (KYC) [cite: 2]</h3>
                      </div>
                       <p class="mb-4 text-sm text-gray-400">
                           @if (Auth::user()->kyc_status === 'pending')
                               Seus documentos estão em análise.
                           @elseif (Auth::user()->kyc_status === 'rejected')
                               Sua verificação foi rejeitada. Por favor, verifique seu e-mail ou envie novamente.
                           @elseif (Auth::user()->kyc_status === 'resubmission_requested')
                               É necessário reenviar seus documentos. Verifique seu e-mail para detalhes.
                           @else {{-- null or other initial status --}}
                                Complete a verificação de identidade para acessar todas as funcionalidades.
                           @endif
                       </p>
                       <x-button.warning href="{{ route('profile.edit') }}#kyc">
                           Verificar Identidade
                       </x-button.warning>
                  </div>
              </x-card>
              @endif

              {{-- Outros cards laterais: Promoções, Notícias (Libertom News [cite: 170]), etc. --}}

        </div>
    </div>
</x-app-layout>