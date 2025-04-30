<x-app-layout>
    <x-slot name="header">
        {{ __('Enviar Dinheiro') }}
    </x-slot>

    <div class="max-w-2xl mx-auto"
         x-data="sendMoneyForm()"> {{-- Alpine.js component to manage steps and data --}}
        <x-card>
            <form method="POST" action="{{ route('sendmoney.submit') }}" @submit.prevent="submitTransfer"> {{-- Rota de exemplo --}}
                @csrf

                {{-- Indicador de Passos --}}
                <div class="p-4 border-b border-gray-700">
                    <nav aria-label="Progress">
                        <ol role="list" class="flex items-center justify-between">
                            <li class="relative pr-8 sm:pr-20">
                                <div class="absolute inset-0 flex items-center" aria-hidden="true" x-show="currentStep > 1">
                                    <div class="h-0.5 w-full bg-amber-500"></div>
                                </div>
                                <a href="#" @click.prevent="currentStep = 1" class="relative flex items-center justify-center w-8 h-8 rounded-full" :class="currentStep >= 1 ? 'bg-amber-500 hover:bg-amber-400' : 'bg-gray-600 hover:bg-gray-500'">
                                    <span class="text-white">1</span>
                                    <span class="absolute top-0 right-0 -mt-10 text-xs text-center text-gray-400 whitespace-nowrap" x-show="currentStep == 1">Valor</span>
                                </a>
                            </li>
                             <li class="relative pr-8 sm:pr-20">
                                 <div class="absolute inset-0 flex items-center" aria-hidden="true" x-show="currentStep > 2">
                                     <div class="h-0.5 w-full bg-amber-500"></div>
                                 </div>
                                <a href="#" @click.prevent="currentStep = 2" class="relative flex items-center justify-center w-8 h-8 rounded-full" :class="currentStep >= 2 ? 'bg-amber-500 hover:bg-amber-400' : 'bg-gray-600 hover:bg-gray-500'">
                                     <span :class="currentStep >= 2 ? 'text-white' : 'text-gray-400'">2</span>
                                     <span class="absolute top-0 right-0 -mt-10 text-xs text-center text-gray-400 whitespace-nowrap" x-show="currentStep == 2">Destino</span>
                                </a>
                            </li>
                             <li class="relative pr-8 sm:pr-20">
                                 <div class="absolute inset-0 flex items-center" aria-hidden="true" x-show="currentStep > 3">
                                     <div class="h-0.5 w-full bg-amber-500"></div>
                                 </div>
                                 <a href="#" @click.prevent="currentStep = 3" class="relative flex items-center justify-center w-8 h-8 rounded-full" :class="currentStep >= 3 ? 'bg-amber-500 hover:bg-amber-400' : 'bg-gray-600 hover:bg-gray-500'">
                                     <span :class="currentStep >= 3 ? 'text-white' : 'text-gray-400'">3</span>
                                     <span class="absolute top-0 right-0 -mt-10 text-xs text-center text-gray-400 whitespace-nowrap" x-show="currentStep == 3">Destinatário</span>
                                 </a>
                            </li>
                             <li>
                                <a href="#" @click.prevent="currentStep = 4" class="relative flex items-center justify-center w-8 h-8 rounded-full" :class="currentStep >= 4 ? 'bg-amber-500 hover:bg-amber-400' : 'bg-gray-600 hover:bg-gray-500'">
                                     <span :class="currentStep >= 4 ? 'text-white' : 'text-gray-400'">4</span>
                                      <span class="absolute top-0 right-0 -mt-10 text-xs text-center text-gray-400 whitespace-nowrap" x-show="currentStep == 4">Revisar</span>
                                 </a>
                            </li>
                        </ol>
                    </nav>
                </div>

                <div class="p-6">
                    {{-- Passo 1: Valor --}}
                    <div x-show="currentStep === 1" x-transition>
                        <h3 class="mb-4 text-lg font-medium text-gray-100">1. De onde e quanto deseja enviar?</h3>
                        <div class="space-y-4">
                            <div>
                                <x-input.label for="sm_source_wallet" value="Da Minha Carteira" />
                                <x-input.select id="sm_source_wallet" name="source_wallet_id" class="block w-full mt-1" x-model="formData.source_wallet_id" @change="updateRatesAndFees()" required>
                                    <option value="">Selecione a carteira de origem...</option>
                                    {{-- Loop $userWallets com saldo > 0 --}}
                                    @foreach ($userWallets->where('balance', '>', 0) as $wallet)
                                        <option value="{{ $wallet->id }}" data-currency="{{ $wallet->currency }}" data-balance="{{ $wallet->balance }}">
                                            {{ $wallet->currency }} (Saldo: {{ number_format($wallet->balance, 2) }})
                                        </option>
                                    @endforeach
                                </x-input.select>
                                <x-input.error :messages="$errors->sendMoney->get('source_wallet_id')" class="mt-2" />
                            </div>
                             <div>
                                <x-input.label for="sm_amount_sent" value="Valor a Enviar" />
                                <x-input.text id="sm_amount_sent" name="amount_sent" type="number" step="0.01" class="block w-full mt-1"
                                               x-model.number="formData.amount_sent" @input.debounce.500ms="updateRatesAndFees()"
                                               placeholder="0.00" required/>
                                 <x-input.error :messages="$errors->sendMoney->get('amount_sent')" class="mt-2" />
                            </div>
                        </div>
                         <div class="mt-6 text-right">
                            <x-button.primary type="button" @click="currentStep = 2" :disabled="!formData.source_wallet_id || !formData.amount_sent > 0">Próximo</x-button.primary>
                         </div>
                    </div>

                    {{-- Passo 2: Destino --}}
                    <div x-show="currentStep === 2" x-transition>
                         <h3 class="mb-4 text-lg font-medium text-gray-100">2. Para onde e qual moeda?</h3>
                         <div class="space-y-4">
                             <div>
                                <x-input.label for="sm_dest_country" value="País de Destino" />
                                <x-input.select id="sm_dest_country" name="destination_country" class="block w-full mt-1" x-model="formData.destination_country" @change="updateRatesAndFees()" required>
                                     <option value="">Selecione o país...</option>
                                     {{-- Popular com lista de países suportados $supportedCountries --}}
                                     @foreach($supportedCountries as $code => $name)
                                         <option value="{{ $code }}">{{ $name }}</option>
                                     @endforeach
                                </x-input.select>
                             </div>
                              <div>
                                <x-input.label for="sm_dest_currency" value="Moeda de Recebimento" />
                                <x-input.select id="sm_dest_currency" name="destination_currency" class="block w-full mt-1" x-model="formData.destination_currency" @change="updateRatesAndFees()" required>
                                     <option value="">Selecione a moeda...</option>
                                     {{-- Popular com moedas suportadas $supportedCurrencies --}}
                                      @foreach($supportedCurrencies as $code)
                                         <option value="{{ $code }}">{{ $code }}</option>
                                     @endforeach
                                </x-input.select>
                             </div>
                             {{-- Detalhes da Conversão e Taxas (atualizado via Alpine/API) --}}
                              <div class="p-4 mt-4 space-y-2 border border-gray-700 rounded-lg bg-gray-800/50" x-show="rateInfo.rate > 0">
                                 <div class="flex justify-between text-sm">
                                     <span class="text-gray-400">Taxa de Câmbio (<span x-text="formData.source_currency"></span> a <span x-text="formData.destination_currency"></span>):</span>
                                     <span class="font-medium text-gray-200" x-text="'1 ' + formData.source_currency + ' = ' + rateInfo.rate.toFixed(4) + ' ' + formData.destination_currency"></span>
                                 </div>
                                 <div class="flex justify-between text-sm">
                                     <span class="text-gray-400">Taxa de Serviço:</span>
                                     <span class="font-medium text-gray-200" x-text="rateInfo.fee.toFixed(2) + ' ' + formData.source_currency"></span>
                                 </div>
                                  <div class="flex justify-between text-sm font-semibold">
                                     <span class="text-gray-300">Valor Total Debitado:</span>
                                     <span class="text-amber-400" x-text="rateInfo.total_debit.toFixed(2) + ' ' + formData.source_currency"></span>
                                 </div>
                                  <div class="flex justify-between text-sm font-semibold">
                                     <span class="text-gray-300">Destinatário Receberá:</span>
                                     <span class="text-green-400" x-text="rateInfo.amount_received.toFixed(2) + ' ' + formData.destination_currency"></span>
                                 </div>
                                 <p class="text-xs text-gray-500">Tempo estimado: <span x-text="rateInfo.estimated_time"></span>. Taxa garantida por <span x-text="rateInfo.quote_valid_seconds / 60"></span> minutos.</p>
                             </div>
                             <div x-show="loadingRates" class="mt-4 text-sm text-center text-gray-500">Calculando taxas...</div>
                         </div>
                          <div class="flex justify-between mt-6">
                             <x-button.secondary type="button" @click="currentStep = 1">Voltar</x-button.secondary>
                             <x-button.primary type="button" @click="currentStep = 3" :disabled="!rateInfo.rate > 0 || loadingRates">Próximo</x-button.primary>
                         </div>
                    </div>

                    {{-- Passo 3: Destinatário --}}
                     <div x-show="currentStep === 3" x-transition>
                         <h3 class="mb-4 text-lg font-medium text-gray-100">3. Quem receberá o dinheiro?</h3>
                         <div class="space-y-4">
                             <div>
                                <x-input.label for="sm_recipient_name" value="Nome Completo do Destinatário" />
                                <x-input.text id="sm_recipient_name" name="recipient_name" class="block w-full mt-1" x-model="formData.recipient_name" required />
                             </div>

                            {{-- Campos Bancários Dinâmicos --}}
                             <div x-html="getRecipientFieldsHtml()">
                                 {{-- O HTML dos campos será injetado aqui pelo Alpine --}}
                                 <p class="text-sm text-gray-500">Selecione o país e moeda de destino para ver os campos necessários.</p>
                             </div>

                             <div>
                                 <x-input.label for="sm_transfer_purpose" value="Propósito da Transferência (Opcional)" />
                                 <x-input.select id="sm_transfer_purpose" name="transfer_purpose" class="block w-full mt-1" x-model="formData.transfer_purpose">
                                     <option value="">Selecione...</option>
                                     <option value="personal_family_support">Suporte Familiar</option>
                                     <option value="personal_gift">Presente</option>
                                     <option value="education">Educação</option>
                                     <option value="goods_services">Pagamento de Bens/Serviços</option>
                                     <option value="investment">Investimento</option>
                                     <option value="other">Outro</option>
                                 </x-input.select>
                             </div>
                             <div>
                                <x-input.label for="sm_reference" value="Referência (Opcional)" />
                                <x-input.text id="sm_reference" name="reference" class="block w-full mt-1" x-model="formData.reference" placeholder="Ex: Pagamento Fatura #123"/>
                             </div>
                         </div>
                         <div class="flex justify-between mt-6">
                              <x-button.secondary type="button" @click="currentStep = 2">Voltar</x-button.secondary>
                              <x-button.primary type="button" @click="currentStep = 4" :disabled="!formData.recipient_name /* Add validation for dynamic fields */">Próximo</x-button.primary>
                         </div>
                    </div>

                    {{-- Passo 4: Revisão e Confirmação --}}
                    <div x-show="currentStep === 4" x-transition>
                         <h3 class="mb-4 text-lg font-medium text-gray-100">4. Revise e Confirme</h3>
                         <div class="p-4 space-y-3 border border-gray-700 rounded-lg bg-gray-800/50">
                              <h4 class="font-semibold text-gray-200">Resumo da Transferência</h4>
                              <div class="flex justify-between text-sm"><span class="text-gray-400">Enviando de:</span> <span class="font-medium" x-text="formData.amount_sent.toFixed(2) + ' ' + formData.source_currency"></span></div>
                              <div class="flex justify-between text-sm"><span class="text-gray-400">Taxa de Câmbio:</span> <span class="font-medium" x-text="'1 ' + formData.source_currency + ' = ' + rateInfo.rate.toFixed(4) + ' ' + formData.destination_currency"></span></div>
                              <div class="flex justify-between text-sm"><span class="text-gray-400">Taxa de Serviço:</span> <span class="font-medium" x-text="rateInfo.fee.toFixed(2) + ' ' + formData.source_currency"></span></div>
                              <div class="flex justify-between text-sm"><span class="text-gray-400">Total Debitado:</span> <span class="font-medium" x-text="rateInfo.total_debit.toFixed(2) + ' ' + formData.source_currency"></span></div>
                              <hr class="border-gray-700">
                              <div class="flex justify-between text-sm"><span class="text-gray-400">Destinatário:</span> <span class="font-medium" x-text="formData.recipient_name"></span></div>
                              {{-- Mostrar detalhes bancários inseridos --}}
                              <div class="flex justify-between text-sm" x-show="formData.recipient_iban"><span class="text-gray-400">IBAN:</span> <span class="font-medium font-mono" x-text="formData.recipient_iban"></span></div>
                              <div class="flex justify-between text-sm" x-show="formData.recipient_swift"><span class="text-gray-400">SWIFT/BIC:</span> <span class="font-medium font-mono" x-text="formData.recipient_swift"></span></div>
                              <div class="flex justify-between text-sm" x-show="formData.recipient_account_number"><span class="text-gray-400">Conta:</span> <span class="font-medium font-mono" x-text="formData.recipient_account_number"></span></div>
                              {{-- Adicionar outros campos dinâmicos aqui --}}
                              <div class="flex justify-between text-sm"><span class="text-gray-400">País/Moeda Dest.:</span> <span class="font-medium"><span x-text="formData.destination_country"></span> / <span x-text="formData.destination_currency"></span></span></div>
                               <div class="flex justify-between text-sm font-semibold"><span class="text-gray-300">Valor a Receber:</span> <span class="text-xl text-green-400" x-text="rateInfo.amount_received.toFixed(2) + ' ' + formData.destination_currency"></span></div>
                         </div>

                         {{-- Senha de Transação --}}
                         <div class="mt-6">
                             <x-input.label for="sm_transaction_password" value="Senha de Transação" />
                             <x-input.text id="sm_transaction_password" name="transaction_password" type="password" class="block w-full mt-1" x-model="formData.transaction_password" required/>
                             <x-input.error :messages="$errors->sendMoney->get('transaction_password')" class="mt-2" />
                             <x-input.error :messages="$errors->sendMoney->get('general')" class="mt-2" /> {{-- Erro geral --}}
                         </div>

                         <div class="flex justify-between mt-6">
                              <x-button.secondary type="button" @click="currentStep = 3">Voltar</x-button.secondary>
                              <x-button.success type="submit" :disabled="loadingSubmit">
                                   <span x-show="!loadingSubmit">Confirmar e Enviar</span>
                                   <span x-show="loadingSubmit">Enviando...</span>
                              </x-button.success>
                         </div>
                    </div>
                </div>

            </form>
        </x-card>
    </div>

    {{-- Alpine.js Logic --}}
    <script>
        function sendMoneyForm() {
            return {
                currentStep: 1,
                loadingRates: false,
                loadingSubmit: false,
                formData: {
                    source_wallet_id: '',
                    source_currency: '',
                    amount_sent: null,
                    destination_country: '',
                    destination_currency: '',
                    recipient_name: '',
                    // Campos dinâmicos do destinatário (exemplos)
                    recipient_iban: '',
                    recipient_swift: '',
                    recipient_account_number: '',
                    recipient_bank_code: '',
                    recipient_branch_code: '',
                    recipient_pix_key: '', // Exemplo Brasil
                    recipient_cbu: '', // Exemplo Argentina
                    // ... outros campos conforme necessário
                    transfer_purpose: '',
                    reference: '',
                    transaction_password: '',
                    quote_id: null // Para garantir a taxa
                },
                rateInfo: {
                    rate: 0,
                    fee: 0,
                    total_debit: 0,
                    amount_received: 0,
                    estimated_time: '',
                    quote_valid_seconds: 0,
                },

                // Mapeamento de campos necessários por país/moeda (simplificado)
                // Idealmente, isso viria de uma API ou config
                requiredFieldsMap: {
                    'DE': { 'EUR': ['recipient_name', 'recipient_iban'] }, // Alemanha
                    'US': { 'USD': ['recipient_name', 'recipient_account_number', 'recipient_swift'] }, // EUA
                    'GB': { 'GBP': ['recipient_name', 'recipient_account_number', 'recipient_swift'] }, // Reino Unido
                    'BR': { 'BRL': ['recipient_name', 'recipient_bank_code', 'recipient_branch_code', 'recipient_account_number'] }, // Brasil (sem PIX)
                    'AR': { 'ARS': ['recipient_name', 'recipient_cbu'] }, // Argentina
                    // Adicionar mais países e moedas
                },

                getRecipientFieldsHtml() {
                    const country = this.formData.destination_country;
                    const currency = this.formData.destination_currency;
                    const fieldsNeeded = this.requiredFieldsMap[country]?.[currency] || [];

                    if (!country || !currency || fieldsNeeded.length === 0) {
                         return '<p class="text-sm text-gray-500">Selecione o país e moeda de destino para ver os campos necessários.</p>';
                    }

                    let html = '<div class="space-y-4 pt-4 border-t border-gray-700">';
                     html += '<h4 class="text-sm font-medium text-gray-300">Detalhes Bancários do Destinatário ('+country+' - '+currency+')</h4>';

                    fieldsNeeded.forEach(fieldName => {
                         if (fieldName === 'recipient_name') return; // Já está fora

                         let label = fieldName.replace('recipient_', '').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                         let type = 'text';
                         let placeholder = '';

                         // Ajustes específicos
                         if (fieldName === 'recipient_iban') { label = 'IBAN'; placeholder = 'DE89 3704 0044 0532 0130 00'; }
                         if (fieldName === 'recipient_swift') { label = 'SWIFT / BIC'; placeholder = 'BANKDEFFXXX'; }
                         if (fieldName === 'recipient_account_number') { label = 'Número da Conta'; }
                         if (fieldName === 'recipient_bank_code') { label = 'Código do Banco'; }
                         if (fieldName === 'recipient_branch_code') { label = 'Agência'; }
                          if (fieldName === 'recipient_cbu') { label = 'CBU (Argentina)'; placeholder = '0170016040000098765432'; }
                          if (fieldName === 'recipient_pix_key') { label = 'Chave PIX (Brasil)'; placeholder = 'email@exemplo.com ou CPF/CNPJ'; }


                         html += `
                            <div>
                                <label for="sm_${fieldName}" class="block text-sm font-medium text-gray-400">${label}</label>
                                <input id="sm_${fieldName}" name="${fieldName}" type="${type}" x-model="formData.${fieldName}"
                                       class="block w-full mt-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-md shadow-sm text-sm text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500"
                                       placeholder="${placeholder}" required>
                                {{-- Adicionar :messages="$errors->sendMoney->get('${fieldName}')" se usar validação específica --}}
                            </div>
                        `;
                    });
                    html += '</div>';
                    return html;
                },

                async updateRatesAndFees() {
                    this.rateInfo = { rate: 0, fee: 0, total_debit: 0, amount_received: 0, estimated_time: '', quote_valid_seconds: 0 }; // Reset
                    this.formData.quote_id = null;

                     // Get source currency from selected wallet option
                     const selectedOption = document.querySelector(`#sm_source_wallet option[value="${this.formData.source_wallet_id}"]`);
                     this.formData.source_currency = selectedOption ? selectedOption.dataset.currency : '';


                     if (!this.formData.source_currency || !this.formData.destination_currency || !this.formData.amount_sent > 0) {
                        return;
                    }

                    this.loadingRates = true;
                    try {
                        // *** IMPORTANTE: Substituir pela chamada real à sua API de cotação ***
                         // Exemplo: const response = await fetch('/api/v1/transfers/quote', { ... });
                         // const data = await response.json();
                        // Exemplo Fixo para demonstração:
                        await new Promise(resolve => setTimeout(resolve, 750)); // Simula delay da API
                         const data = this.getDemoQuote(this.formData.source_currency, this.formData.destination_currency, this.formData.amount_sent);


                         if (data && data.rate > 0) {
                            this.rateInfo = data;
                            this.formData.quote_id = data.quote_id; // Armazena o ID da cotação
                        } else {
                             console.error("Failed to get quote");
                             // Exibir erro para o usuário
                        }
                    } catch (error) {
                        console.error("Error fetching rates:", error);
                         // Exibir erro para o usuário
                    } finally {
                        this.loadingRates = false;
                    }
                },

                // *** Função de Cotação DEMO - Substituir por chamada API real ***
                getDemoQuote(source, dest, amount) {
                     if (!source || !dest || !amount) return null;
                     // Simulação muito básica
                     let rate = 1;
                     if (source === 'USD' && dest === 'EUR') rate = 0.92;
                     if (source === 'USD' && dest === 'BRL') rate = 5.10;
                     if (source === 'BRL' && dest === 'USD') rate = 0.19;
                     if (source === 'EUR' && dest === 'USD') rate = 1.08;
                     // etc...
                     if (rate === 1 && source !== dest) rate = 0.8; // fallback demo

                     const fee = amount * 0.01; // Taxa de 1% (exemplo)
                     const totalDebit = amount + fee;
                     const amountReceived = (amount * rate);

                     return {
                        rate: rate,
                        fee: fee,
                        total_debit: totalDebit,
                        amount_received: amountReceived,
                        estimated_time: '1-2 dias úteis',
                        quote_valid_seconds: 300, // 5 minutos
                        quote_id: 'DEMO_' + Date.now() // ID Fixo de Cotação
                    };
                },

                async submitTransfer() {
                    this.loadingSubmit = true;
                    const formElement = this.$refs.sendMoneyForm; // Adicionar ref="sendMoneyForm" ao <form>

                    // *** IMPORTANTE: Preparar dados e enviar para a API de submissão ***
                    // Exemplo: const response = await fetch(formElement.action, { method: 'POST', body: new FormData(formElement) });
                     console.log("Submitting Data:", this.formData);
                     await new Promise(resolve => setTimeout(resolve, 1500)); // Simular envio
                     this.loadingSubmit = false;

                     // Tratar resposta da API (sucesso ou erro)
                     // Exemplo Sucesso: window.location.href = '/dashboard/transactions/success';
                     // Exemplo Erro: alert('Erro ao enviar: ' + result.message);
                     alert('Transferência submetida (simulação). Redirecionar para página de sucesso/erro.');
                     // Resetar form se necessário: this.resetForm();
                },

                 resetForm() {
                     this.currentStep = 1;
                     this.formData = { /* valores iniciais */ };
                     this.rateInfo = { /* valores iniciais */ };
                 }
            }
        }
    </script>
</x-app-layout>