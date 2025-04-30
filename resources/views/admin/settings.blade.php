@extends('layouts.admin')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold text-gray-100">Configurações da Plataforma</h1>

    <div x-data="{ tab: 'general' }" class="space-y-6">
         {{-- Navegação das Abas --}}
        <div class="mb-6 border-b border-gray-700">
            <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                <button @click="tab = 'general'" :class="{ 'border-amber-500 text-amber-400': tab === 'general', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'general' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Geral
                </button>
                <button @click="tab = 'admin_url'" :class="{ 'border-amber-500 text-amber-400': tab === 'admin_url', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'admin_url' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   URL Admin
                </button>
                <button @click="tab = 'gateways'" :class="{ 'border-amber-500 text-amber-400': tab === 'gateways', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'gateways' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Gateways Pagamento
                </button>
                 <button @click="tab = 'withdrawals'" :class="{ 'border-amber-500 text-amber-400': tab === 'withdrawals', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'withdrawals' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Saques
                </button>
                 {{-- Adicionar aba para Funcionários/ACL se necessário --}}
            </nav>
        </div>

         {{-- Aba: Geral --}}
        <div x-show="tab === 'general'" x-cloak>
             <form method="POST" action="{{ route('admin.settings.updateGeneral') }}">
                @csrf
                @method('PUT')
                 <x-card>
                    <div class="p-6 space-y-4">
                         <div>
                             <x-input.label for="site_name" value="Nome do Site" />
                             <x-input.text id="site_name" name="site_name" :value="old('site_name', config('app.name'))" class="mt-1 block w-full" required />
                         </div>
                          {{-- Exemplo de Toggle para Modo Manutenção (Componente <x-input.toggle>) --}}
                          <x-input.toggle name="maintenance_mode" label="Modo Manutenção" :checked="app()->isDownForMaintenance()" />
                    </div>
                    <div class="px-6 py-4 bg-gray-750 text-right">
                        <x-button.primary type="submit">Salvar Configurações Gerais</x-button.primary>
                    </div>
                 </x-card>
             </form>
        </div>

        {{-- Aba: URL Admin --}}
        <div x-show="tab === 'admin_url'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.updateAdminUrl') }}">
                @csrf
                @method('PUT')
                <x-card>
                     <div class="p-6 space-y-4">
                         <p class="text-sm text-gray-400">Defina o caminho personalizado para acessar o painel administrativo.</p>
                         <p class="text-sm text-yellow-400"><x-heroicon-o-exclamation class="inline w-4 h-4 mr-1"/>Alterar esta URL irá desconectar sua sessão atual e você precisará acessar pelo novo caminho.</p>
                         <div>
                             <x-input.label for="admin_url_path" value="Novo Caminho da URL (sem barras)" />
                             <div class="flex mt-1">
                                 <span class="inline-flex items-center px-3 text-sm text-gray-400 bg-gray-700 border border-r-0 border-gray-600 rounded-l-md">
                                     {{ config('app.url') }}/
                                 </span>
                                 <x-input.text id="admin_url_path" name="admin_url_path" :value="old('admin_url_path', config('app.admin_route_prefix'))" class="rounded-l-none flex-1 block w-full" placeholder="ex: painel-seguro" required />
                             </div>
                             <x-input.error :messages="$errors->get('admin_url_path')" class="mt-2" />
                         </div>
                     </div>
                     <div class="px-6 py-4 bg-gray-750 text-right">
                        <x-button.warning type="submit">Atualizar URL Admin</x-button.warning>
                     </div>
                </x-card>
            </form>
        </div>

         {{-- Aba: Gateways de Pagamento --}}
        <div x-show="tab === 'gateways'" x-cloak>
             <form method="POST" action="{{ route('admin.settings.updateGateways') }}">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    {{-- Configuração Banco Cora --}}
                    <x-card>
                        <div class="p-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-100">Banco Cora (Brasil)</h3>
                             <x-input.toggle name="cora_enabled" label="Habilitar Cora" :checked="old('cora_enabled', config('cora.enabled'))" />
                            <div>
                                <x-input.label for="cora_client_id" value="Client ID" />
                                <x-input.text id="cora_client_id" name="cora_client_id" :value="old('cora_client_id', config('cora.client_id'))" class="mt-1 block w-full" />
                            </div>
                            <div>
                                 <x-input.label for="cora_client_secret" value="Client Secret" />
                                 <x-input.text id="cora_client_secret" name="cora_client_secret" type="password" value="************" class="mt-1 block w-full" placeholder="Preencha para alterar"/>
                                 <p class="text-xs text-gray-500 mt-1">Deixe em branco para manter o segredo atual.</p>
                            </div>
                            <div>
                                <x-input.label for="cora_webhook_url" value="Webhook URL (Leitura)" />
                                <x-input.text id="cora_webhook_url" :value="route('webhooks.cora')" class="mt-1 block w-full bg-gray-700 cursor-not-allowed" readonly />
                                <p class="text-xs text-gray-500 mt-1">Configure este URL no painel do Cora para receber notificações.</p>
                            </div>
                        </div>
                    </x-card>

                     {{-- Configuração Stripe --}}
                     <x-card>
                         <div class="p-6 space-y-4">
                             <h3 class="text-lg font-medium text-gray-100">Stripe (Internacional)</h3>
                             <x-input.toggle name="stripe_enabled" label="Habilitar Stripe" :checked="old('stripe_enabled', config('stripe.enabled'))" />
                              <div>
                                 <x-input.label for="stripe_key" value="Publishable Key" />
                                 <x-input.text id="stripe_key" name="stripe_key" :value="old('stripe_key', config('stripe.key'))" class="mt-1 block w-full" />
                             </div>
                             <div>
                                  <x-input.label for="stripe_secret" value="Secret Key" />
                                  <x-input.text id="stripe_secret" name="stripe_secret" type="password" value="************" class="mt-1 block w-full" placeholder="Preencha para alterar"/>
                                  <p class="text-xs text-gray-500 mt-1">Deixe em branco para manter o segredo atual.</p>
                             </div>
                              <div>
                                <x-input.label for="stripe_webhook_secret" value="Webhook Signing Secret" />
                                <x-input.text id="stripe_webhook_secret" name="stripe_webhook_secret" type="password" value="************" class="mt-1 block w-full" placeholder="Preencha para alterar"/>
                                <p class="text-xs text-gray-500 mt-1">Deixe em branco para manter o segredo atual. Obtenha no painel do Stripe.</p>
                            </div>
                            <div>
                                <x-input.label for="stripe_webhook_url" value="Webhook URL (Leitura)" />
                                <x-input.text id="stripe_webhook_url" :value="route('webhooks.stripe')" class="mt-1 block w-full bg-gray-700 cursor-not-allowed" readonly />
                                <p class="text-xs text-gray-500 mt-1">Configure este URL no painel do Stripe para receber eventos.</p>
                            </div>
                         </div>
                     </x-card>

                     {{-- Botão de Salvar --}}
                     <div class="text-right">
                         <x-button.primary type="submit">Salvar Configurações dos Gateways</x-button.primary>
                     </div>
                </div>
             </form>
        </div>

         {{-- Aba: Saques --}}
        <div x-show="tab === 'withdrawals'" x-cloak>
            <form method="POST" action="{{ route('admin.settings.updateWithdrawals') }}">
                @csrf
                @method('PUT')
                <x-card>
                     <div class="p-6 space-y-4">
                         <h3 class="text-lg font-medium text-gray-100">Configurações de Saque</h3>
                          <x-input.toggle name="withdrawal_auto_approval" label="Aprovação Automática de Saques" :checked="old('withdrawal_auto_approval', config('libertom.withdrawals.auto_approve'))" />
                          <div x-data="{ enabled: {{ config('libertom.withdrawals.auto_approve') ? 'true' : 'false' }} }" x-init="$watch('$store.app.withdrawal_auto_approval', value => enabled = value)">
                             <div x-show="enabled" class="mt-4 pl-6 border-l-2 border-gray-700" x-cloak>
                                 <x-input.label for="withdrawal_auto_approval_limit_usd" value="Limite para Aprovação Automática (USD)" />
                                 <x-input.text id="withdrawal_auto_approval_limit_usd" name="withdrawal_auto_approval_limit_usd" type="number" step="0.01" :value="old('withdrawal_auto_approval_limit_usd', config('libertom.withdrawals.auto_approve_limit_usd'))" class="mt-1 block w-full sm:w-1/2" placeholder="Ex: 500.00" />
                                 <p class="text-xs text-gray-500 mt-1">Saques abaixo deste valor (em USD equivalente) serão aprovados automaticamente se a opção estiver ativa.</p>
                             </div>
                         </div>
                         {{-- Adicionar outras configurações de saque, como taxas, limites mínimos/máximos, etc. --}}
                     </div>
                     <div class="px-6 py-4 bg-gray-750 text-right">
                        <x-button.primary type="submit">Salvar Configurações de Saque</x-button.primary>
                     </div>
                </x-card>
            </form>
        </div>
    </div>
@endsection

{{-- Placeholder para Componentes (Exemplo) --}}
{{-- resources/views/components/input/toggle.blade.php (Alpine.js) --}}
{{-- @props(['name', 'label', 'checked' => false])
<div class="flex items-center" x-data="{ on: {{ $checked ? 'true' : 'false' }} }">
    <label :for="'toggle-' + '{{ $name }}'" class="flex items-center cursor-pointer">
        <span class="relative">
            <input :id="'toggle-' + '{{ $name }}'" type="checkbox" class="sr-only" name="{{ $name }}" x-model="on">
            <span class="block w-10 h-6 bg-gray-600 rounded-full shadow-inner"></span>
            <span class="absolute block w-4 h-4 mt-1 ml-1 transform bg-white rounded-full shadow inset-y-0 left-0 focus-within:shadow-outline transition-transform duration-300 ease-in-out"
                  :class="{ 'translate-x-full !bg-amber-400': on, 'bg-gray-400': !on }">
             </span>
        </span>
        <span class="ml-3 text-sm font-medium text-gray-300">{{ $label }}</span>
    </label>
</div> --}}