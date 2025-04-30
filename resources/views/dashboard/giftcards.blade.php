<x-app-layout>
    <x-slot name="header">
        {{ __('Gift Cards Libertom') }}
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Coluna Principal (Comprar e Resgatar) --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Card para Comprar Gift Card --}}
            <x-card>
                <form method="POST" action="{{ route('giftcards.purchase') }}"> {{-- Rota de exemplo --}}
                    @csrf
                    <div class="p-6 space-y-4">
                        <h3 class="text-lg font-medium text-gray-100">Comprar Gift Card</h3>
                         <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            {{-- Selecionar Moeda e Valor --}}
                             <div>
                                 <x-input.label for="gc_currency" value="Moeda do Gift Card" />
                                 <x-input.select id="gc_currency" name="currency" class="block w-full mt-1" required>
                                     {{-- Popular com moedas disponíveis para gift card (ex: USD, BRL) --}}
                                     <option value="USD">USD</option>
                                     <option value="BRL">BRL</option>
                                     <option value="EUR">EUR</option>
                                 </x-input.select>
                                 <x-input.error :messages="$errors->purchaseGiftCard->get('currency')" class="mt-2" />
                             </div>
                             <div>
                                <x-input.label for="gc_amount" value="Valor" />
                                <x-input.text id="gc_amount" name="amount" type="number" step="1" min="10" class="block w-full mt-1" placeholder="Ex: 50" required/>
                                <x-input.error :messages="$errors->purchaseGiftCard->get('amount')" class="mt-2" />
                            </div>
                        </div>
                         {{-- Informações do Destinatário (Opcional) --}}
                         <div>
                            <x-input.label for="gc_recipient_email" value="Enviar para (Email - Opcional)" />
                            <x-input.text id="gc_recipient_email" name="recipient_email" type="email" class="block w-full mt-1" placeholder="email@destinatario.com"/>
                             <p class="mt-1 text-xs text-gray-500">Deixe em branco para comprar para você mesmo.</p>
                             <x-input.error :messages="$errors->purchaseGiftCard->get('recipient_email')" class="mt-2" />
                        </div>
                         <div>
                            <x-input.label for="gc_message" value="Mensagem (Opcional)" />
                            <x-input.textarea id="gc_message" name="message" rows="2" class="block w-full mt-1" placeholder="Feliz aniversário!"/>
                             <x-input.error :messages="$errors->purchaseGiftCard->get('message')" class="mt-2" />
                        </div>
                         {{-- Selecionar Carteira para Pagamento --}}
                         <div>
                             <x-input.label for="gc_payment_wallet" value="Pagar com a Carteira" />
                             <x-input.select id="gc_payment_wallet" name="payment_wallet_id" class="block w-full mt-1" required>
                                 {{-- Loop através das carteiras do usuário com saldo suficiente --}}
                                  @foreach ($userWallets as $wallet)
                                      <option value="{{ $wallet->id }}">{{ $wallet->currency }} - Saldo: {{ number_format($wallet->balance, 2) }}</option>
                                  @endforeach
                             </x-input.select>
                             <x-input.error :messages="$errors->purchaseGiftCard->get('payment_wallet_id')" class="mt-2" />
                         </div>
                         {{-- Confirmação e Senha de Transação (pode ser em modal) --}}
                         <div class="pt-4 border-t border-gray-700">
                             <x-input.label for="gc_transaction_password" value="Senha de Transação" />
                             <x-input.text id="gc_transaction_password" name="transaction_password" type="password" class="block w-full mt-1" required/>
                             <x-input.error :messages="$errors->purchaseGiftCard->get('transaction_password')" class="mt-2" />
                         </div>

                    </div>
                    <div class="px-6 py-4 bg-gray-750 text-right">
                        {{-- Mostrar o custo estimado aqui antes de confirmar --}}
                        <x-button.primary type="submit">Confirmar Compra</x-button.primary>
                    </div>
                </form>
            </x-card>

             {{-- Card para Resgatar Gift Card --}}
             <x-card>
                 <form method="POST" action="{{ route('giftcards.redeem') }}"> {{-- Rota de exemplo --}}
                     @csrf
                     <div class="p-6 space-y-4">
                         <h3 class="text-lg font-medium text-gray-100">Resgatar Gift Card</h3>
                         <div>
                            <x-input.label for="gc_code" value="Código do Gift Card" />
                            <x-input.text id="gc_code" name="code" type="text" class="block w-full mt-1 uppercase" placeholder="XXXX-XXXX-XXXX-XXXX" required/>
                            <x-input.error :messages="$errors->redeemGiftCard->get('code')" class="mt-2" />
                        </div>
                     </div>
                     <div class="px-6 py-4 bg-gray-750 text-right">
                        <x-button.success type="submit">Resgatar Código</x-button.success>
                     </div>
                 </form>
            </x-card>
        </div>

        {{-- Coluna Lateral (Meus Gift Cards) --}}
        <div class="space-y-6 lg:col-span-1">
            {{-- Meus Gift Cards Recebidos / Comprados para Mim --}}
            <x-card>
                <div class="p-4 border-b border-gray-700">
                    <h3 class="font-semibold text-gray-100">Meus Gift Cards</h3>
                </div>
                <div class="divide-y divide-gray-700 max-h-96 overflow-y-auto">
                    @forelse ($myGiftCards as $card)
                        <div class="p-4 hover:bg-gray-750">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm text-gray-300">****-****-{{ substr($card->code, -4) }}</span>
                                <x-badge :color="$card->status == 'active' ? 'green' : ($card->status == 'redeemed' ? 'gray' : 'red')">
                                    {{ ucfirst($card->status) }}
                                </x-badge>
                            </div>
                            <p class="mt-1 text-lg font-semibold text-white">
                                {{ $card->currency }} {{ number_format($card->balance, 2) }}
                                @if($card->balance < $card->initial_value)
                                    <span class="text-xs font-normal text-gray-400">(de {{ number_format($card->initial_value, 2) }})</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">
                                @if($card->was_purchased_by_user) {{-- Flag vinda do backend --}}
                                    Comprado em: {{ $card->created_at->format('d/m/Y') }}
                                @else
                                    Recebido de: {{ $card->sender_name ?? 'Libertom' }} em {{ $card->created_at->format('d/m/Y') }}
                                @endif
                                @if ($card->expires_at) | Expira em: {{ $card->expires_at->format('d/m/Y') }} @endif
                            </p>
                            {{-- Adicionar botão de detalhes/ver código completo se status for 'active' --}}
                        </div>
                    @empty
                        <p class="p-4 text-sm text-center text-gray-500">Você não possui nenhum gift card ativo.</p>
                    @endforelse
                </div>
            </x-card>

             {{-- Gift Cards Enviados --}}
             <x-card>
                 <div class="p-4 border-b border-gray-700">
                     <h3 class="font-semibold text-gray-100">Gift Cards Enviados</h3>
                 </div>
                 <div class="divide-y divide-gray-700 max-h-96 overflow-y-auto">
                      @forelse ($sentGiftCards as $card)
                         <div class="p-4 hover:bg-gray-750">
                             <div class="flex items-center justify-between">
                                 <span class="text-sm text-gray-300 truncate" title="{{ $card->recipient_email }}">Para: {{ $card->recipient_email }}</span>
                                 <x-badge :color="$card->status == 'redeemed' ? 'blue' : 'gray'">
                                     {{ $card->status == 'redeemed' ? 'Resgatado' : 'Enviado' }}
                                 </x-badge>
                             </div>
                             <p class="mt-1 text-lg font-semibold text-white">
                                 {{ $card->currency }} {{ number_format($card->initial_value, 2) }}
                             </p>
                             <p class="text-xs text-gray-500">Enviado em: {{ $card->created_at->format('d/m/Y') }}</p>
                             {{-- Adicionar botão para ver detalhes/reenviar email --}}
                         </div>
                      @empty
                         <p class="p-4 text-sm text-center text-gray-500">Você ainda não enviou nenhum gift card.</p>
                      @endforelse
                 </div>
             </x-card>

        </div>
    </div>
</x-app-layout>