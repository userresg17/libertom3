@extends('layouts.admin')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-gray-100">Editar Cliente: {{ $user->name }}</h1>

    {{-- Usar abas ou seções separadas --}}
    <div x-data="{ tab: 'profile' }">
        {{-- Navegação das Abas --}}
        <div class="mb-6 border-b border-gray-700">
            <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                <button @click="tab = 'profile'" :class="{ 'border-amber-500 text-amber-400': tab === 'profile', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'profile' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Perfil e Status
                </button>
                <button @click="tab = 'balances'" :class="{ 'border-amber-500 text-amber-400': tab === 'balances', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'balances' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Saldos e Ajustes
                </button>
                <button @click="tab = 'kyc'" :class="{ 'border-amber-500 text-amber-400': tab === 'kyc', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'kyc' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Documentos (KYC)
                </button>
                 <button @click="tab = 'history'" :class="{ 'border-amber-500 text-amber-400': tab === 'history', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'history' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Histórico
                </button>
            </nav>
        </div>

        {{-- Conteúdo das Abas --}}
        <div>
            {{-- Aba: Perfil e Status --}}
            <div x-show="tab === 'profile'" x-cloak>
                <form method="POST" action="{{ route('admin.users.update', $user) }}">
                    @csrf
                    @method('PUT')
                    <x-card>
                        <div class="p-6 space-y-6">
                            <div>
                                <x-input.label for="name" value="Nome Completo" />
                                <x-input.text id="name" name="name" type="text" class="block w-full mt-1" :value="old('name', $user->name)" required autofocus />
                                <x-input.error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input.label for="email" value="Email" />
                                <x-input.text id="email" name="email" type="email" class="block w-full mt-1 bg-gray-700 cursor-not-allowed" :value="$user->email" disabled readonly />
                                <p class="mt-1 text-xs text-gray-500">O email não pode ser alterado pelo admin.</p>
                            </div>

                             <div>
                                <x-input.label for="status" value="Status da Conta" />
                                <x-input.select id="status" name="status" class="block w-full mt-1">
                                    <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>Ativo</option>
                                    <option value="pending_kyc" {{ old('status', $user->status) == 'pending_kyc' ? 'selected' : '' }}>Pendente KYC</option>
                                    <option value="blocked" {{ old('status', $user->status) == 'blocked' ? 'selected' : '' }}>Bloqueado</option>
                                    {{-- Outros status se necessário --}}
                                </x-input.select>
                                <x-input.error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                             {{-- Adicionar outros campos do perfil se houver: Telefone, Data Nasc, etc. --}}

                        </div>
                         <div class="px-6 py-4 bg-gray-750 text-right">
                            <x-button.primary type="submit">Salvar Alterações</x-button.primary>
                         </div>
                    </x-card>
                </form>
            </div>

            {{-- Aba: Saldos e Ajustes --}}
            <div x-show="tab === 'balances'" x-cloak>
                 <x-card class="mb-6">
                     <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-100">Saldos Atuais</h3>
                         <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {{-- Saldos Fiat --}}
                            <div>
                                <h4 class="mb-2 font-semibold text-gray-300">Moedas Fiat</h4>
                                <ul class="space-y-1 text-sm">
                                    @forelse ($user->wallets as $wallet)
                                        <li class="flex justify-between">
                                            <span>{{ $wallet->currency }}:</span>
                                            <span>{{ number_format($wallet->balance, 2) }}</span>
                                        </li>
                                    @empty
                                        <li class="text-gray-500">Nenhuma carteira fiat.</li>
                                    @endforelse
                                </ul>
                            </div>
                            {{-- Saldo GoldStay --}}
                            <div>
                                 <h4 class="mb-2 font-semibold text-gray-300">Token GoldStay</h4>
                                 <p class="text-sm">
                                     {{ number_format($user->goldStayWallet->balance ?? 0, 8) }} GST
                                 </p>
                            </div>
                        </div>
                     </div>
                 </x-card>

                <form method="POST" action="{{ route('admin.users.adjustBalance', $user) }}">
                    @csrf
                    <x-card>
                        <div class="p-6 space-y-6">
                            <h3 class="text-lg font-medium text-gray-100">Ajustar Saldos (Crédito/Débito Manual)</h3>
                            <p class="text-sm text-yellow-400"><x-heroicon-o-exclamation class="inline w-4 h-4 mr-1"/> Cuidado: Esta ação afeta diretamente o saldo do cliente e deve ser usada com extrema cautela.</p>

                            {{-- Ajuste Fiat --}}
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <x-input.label for="fiat_currency" value="Moeda Fiat"/>
                                    <x-input.select id="fiat_currency" name="fiat_currency" class="block w-full mt-1">
                                        <option value="">Selecione...</option>
                                        {{-- Popular com moedas disponíveis --}}
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="BRL">BRL</option>
                                        {{-- etc --}}
                                    </x-input.select>
                                </div>
                                <div>
                                     <x-input.label for="fiat_amount" value="Valor (+/-)"/>
                                     <x-input.text id="fiat_amount" name="fiat_amount" type="number" step="0.01" class="block w-full mt-1" placeholder="Ex: 100.50 ou -50.00"/>
                                </div>
                                <div>
                                    <x-input.label for="fiat_reason" value="Motivo do Ajuste Fiat"/>
                                    <x-input.text id="fiat_reason" name="fiat_reason" type="text" class="block w-full mt-1" placeholder="Ex: Correção de depósito"/>
                                </div>
                            </div>

                             {{-- Ajuste GoldStay --}}
                             <div class="grid grid-cols-1 gap-4 pt-4 border-t border-gray-700 sm:grid-cols-3">
                                <div>
                                    <x-input.label value="Token" class="opacity-50"/>
                                    <p class="mt-1 text-gray-300">GoldStay (GST)</p>
                                </div>
                                <div>
                                     <x-input.label for="gst_amount" value="Quantidade (+/-)"/>
                                     <x-input.text id="gst_amount" name="gst_amount" type="number" step="0.00000001" class="block w-full mt-1" placeholder="Ex: 1.5 ou -0.25"/>
                                </div>
                                <div>
                                    <x-input.label for="gst_reason" value="Motivo do Ajuste GST"/>
                                    <x-input.text id="gst_reason" name="gst_reason" type="text" class="block w-full mt-1" placeholder="Ex: Bônus ou Reversão"/>
                                </div>
                            </div>
                             <x-input.error :messages="$errors->get('fiat_currency') ?: $errors->get('fiat_amount') ?: $errors->get('fiat_reason') ?: $errors->get('gst_amount') ?: $errors->get('gst_reason')" class="mt-2" />
                        </div>
                        <div class="px-6 py-4 bg-gray-750 text-right">
                            <x-button.warning type="submit">Aplicar Ajuste</x-button.warning> {{-- Botão diferente para alerta --}}
                        </div>
                    </x-card>
                </form>
            </div>

            {{-- Aba: Documentos (KYC) --}}
            <div x-show="tab === 'kyc'" x-cloak>
                <x-card>
                    <div class="p-6">
                         <h3 class="mb-4 text-lg font-medium text-gray-100">Verificação de Identidade (KYC)</h3>
                         <p class="mb-6 text-sm text-gray-400">Status Atual:
                             <x-badge :color="$user->kyc_status === 'approved' ? 'green' : ($user->kyc_status === 'pending' ? 'yellow' : ($user->kyc_status === 'rejected' ? 'red' : 'gray'))">
                                 {{ ucfirst(str_replace('_', ' ', $user->kyc_status ?? 'Não Enviado')) }}
                             </x-badge>
                         </p>

                         @if($user->kycDocuments->isNotEmpty())
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                @foreach ($user->kycDocuments as $doc)
                                    <div class="p-4 border border-gray-700 rounded-lg bg-gray-750">
                                        <p class="mb-2 font-medium text-gray-300 capitalize">{{ str_replace('_', ' ', $doc->type) }}</p>
                                         {{-- Assumindo que $doc->file_path leva à URL da imagem --}}
                                         <img src="{{ Storage::url($doc->file_path) }}" alt="Documento {{ $doc->type }}" class="object-cover w-full mb-2 rounded h-40 cursor-pointer" @click="window.open('{{ Storage::url($doc->file_path) }}')">
                                         <p class="text-xs text-gray-500">Enviado em: {{ $doc->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Ações do KYC --}}
                             @if($user->kyc_status === 'pending' || $user->kyc_status === 'resubmission_requested')
                             <form method="POST" action="{{ route('admin.users.updateKycStatus', $user) }}" class="mt-6 border-t border-gray-700 pt-6" x-data="{ action: '', reason: '' }">
                                 @csrf
                                 <h4 class="mb-2 font-semibold text-gray-200">Ações de Verificação</h4>
                                 <div class="flex flex-wrap items-start gap-4">
                                     <x-button.success @click="action = 'approved'; $nextTick(() => $refs.form.submit())">
                                         <x-heroicon-o-check-circle class="w-4 h-4 mr-1"/> Aprovar KYC
                                     </x-button.success>

                                     <x-button.danger @click="action = 'rejected'">
                                          <x-heroicon-o-x-circle class="w-4 h-4 mr-1"/> Rejeitar KYC
                                      </x-button.danger>

                                     <x-button.warning @click="action = 'resubmission_requested'">
                                          <x-heroicon-o-exclamation-circle class="w-4 h-4 mr-1"/> Solicitar Reenvio
                                      </x-button.warning>
                                 </div>

                                  {{-- Campo de motivo para Rejeição/Reenvio --}}
                                  <div x-show="action === 'rejected' || action === 'resubmission_requested'" class="mt-4" x-cloak>
                                      <x-input.label for="rejection_reason" :value="action === 'rejected' ? 'Motivo da Rejeição' : 'Motivo da Solicitação de Reenvio'" />
                                      <x-input.textarea id="rejection_reason" name="reason" x-model="reason" class="block w-full mt-1" rows="3" required></x-input.textarea>
                                      <div class="mt-2 text-right">
                                           <x-button.primary type="submit">Confirmar Ação</x-button.primary>
                                      </div>
                                  </div>
                                  <input type="hidden" name="status" x-model="action">
                                  <x-input.error :messages="$errors->get('status') ?: $errors->get('reason')" class="mt-2" />
                             </form>
                             @endif

                         @else
                            <p class="text-gray-500">Nenhum documento KYC foi enviado por este cliente ainda.</p>
                         @endif
                    </div>
                </x-card>
            </div>

             {{-- Aba: Histórico --}}
             <div x-show="tab === 'history'" x-cloak>
                 <x-card>
                     <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium text-gray-100">Histórico de Transações e Atividades</h3>
                        {{-- Aqui viria uma tabela ou lista com o histórico do usuário --}}
                        {{-- Exemplo: Últimos logins, alterações de perfil, transações financeiras, operações de investimento --}}
                         <x-table :headers="['Data', 'Tipo', 'Descrição', 'Detalhes']">
                            {{-- Loop através do histórico $user->activityLogs ou $user->transactions --}}
                            <tr>
                                <x-table.td colspan="4" class="text-center text-gray-500">Histórico ainda não implementado nesta view.</x-table.td>
                            </tr>
                         </x-table>
                     </div>
                 </x-card>
             </div>

        </div>
    </div>
@endsection

{{-- Placeholders para Componentes (Exemplo) --}}
{{-- resources/views/components/input/label.blade.php --}}
{{-- <label {{ $attributes->merge(['class' => 'block text-sm font-medium text-gray-300']) }}>
    {{ $value ?? $slot }}
</label> --}}

{{-- resources/views/components/input/error.blade.php --}}
{{-- @props(['messages'])
@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs text-red-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif --}}

{{-- resources/views/components/input/textarea.blade.php --}}
{{-- <textarea {{ $attributes->merge(['class' => 'block w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md shadow-sm text-sm text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-amber-500 focus:border-amber-500']) }}></textarea> --}}

{{-- resources/views/components/button/success.blade.php --}}
{{-- <button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> --}}

{{-- resources/views/components/button/danger.blade.php --}}
{{-- <button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 active:bg-red-800 focus:outline-none focus:border-red-800 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> --}}

{{-- resources/views/components/button/warning.blade.php --}}
{{-- <button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-yellow-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 active:bg-yellow-800 focus:outline-none focus:border-yellow-800 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> --}}