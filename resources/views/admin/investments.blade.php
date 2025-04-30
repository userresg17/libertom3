@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-6" x-data="{ showCreateModal: false, editAssetData: null }">
        <h1 class="text-2xl font-semibold text-gray-100">Gerenciar Ativos de Investimento</h1>
        <x-button.primary @click="editAssetData = null; showCreateModal = true">
            <x-heroicon-o-plus class="w-4 h-4 mr-1"/> Criar Novo Ativo
        </x-button.primary>

        {{-- Modal de Criação/Edição de Ativo (Componente <x-modal.form>) --}}
        <x-modal.form show="showCreateModal" @close="showCreateModal = false; editAssetData = null" :title="editAssetData ? 'Editar Ativo' : 'Criar Novo Ativo'">
            {{-- Usaremos Alpine.js para gerenciar os dados do formulário e as regras --}}
            <form x-data="assetForm(editAssetData)"
                  @submit.prevent="submitForm(editAssetData ? '{{ route('admin.investments.update', ':id') }}'.replace(':id', editAssetData.id) : '{{ route('admin.investments.store') }}', editAssetData ? 'PUT' : 'POST')"
                  x-init="$watch('editAssetData', value => initializeForm(value))">
                @csrf
                <input type="hidden" name="_method" :value="editAssetData ? 'PUT' : 'POST'">

                <div class="space-y-4">
                    {{-- Campos do Ativo --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <x-input.label for="name" value="Nome do Ativo" />
                            <x-input.text id="name" name="name" x-model="formData.name" required />
                        </div>
                         <div>
                            <x-input.label for="symbol" value="Símbolo/Ticker" />
                            <x-input.text id="symbol" name="symbol" x-model="formData.symbol" required />
                        </div>
                    </div>
                     <div>
                        <x-input.label for="description" value="Descrição" />
                        <x-input.textarea id="description" name="description" x-model="formData.description" rows="3"/>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                             <x-input.label for="type" value="Tipo" />
                             <x-input.select id="type" name="type" x-model="formData.type" required>
                                 <option value="stock">Ação (Stock)</option>
                                 <option value="etf">ETF</option>
                                 <option value="bond">Título (Bond)</option>
                                 {{-- Outros tipos --}}
                             </x-input.select>
                        </div>
                        <div>
                             <x-input.label for="current_price" value="Preço Atual (USD)" />
                             <x-input.text id="current_price" name="current_price" type="number" step="0.01" x-model="formData.current_price" required />
                        </div>
                        <div>
                             <x-input.label for="logo_url" value="URL do Logo" />
                             <x-input.text id="logo_url" name="logo_url" type="url" x-model="formData.logo_url" placeholder="https://..." />
                             {{-- Ou um campo de upload de imagem --}}
                        </div>
                    </div>
                     <div>
                        <x-input.label for="operating_fee" value="Taxa de Operação (%)" />
                        <x-input.text id="operating_fee" name="operating_fee" type="number" step="0.01" x-model="formData.operating_fee" placeholder="Ex: 0.5" />
                    </div>

                    {{-- Regras de Variação Automática --}}
                    <div class="pt-4 border-t border-gray-700">
                        <h4 class="mb-2 font-medium text-gray-200">Regras de Variação Automática</h4>
                         <div class="space-y-2 max-h-40 overflow-y-auto pr-2"> {{-- Adiciona scroll se muitas regras --}}
                             <template x-for="(rule, index) in formData.rules" :key="index">
                                 <div class="flex items-center gap-2 p-2 border border-gray-600 rounded bg-gray-750">
                                     <span class="text-sm">Às</span>
                                     {{-- Input de tempo --}}
                                     <input type="time" x-model="rule.time" class="w-24 text-xs bg-gray-700 border-gray-600 rounded focus:ring-amber-500 focus:border-amber-500">
                                     <span class="text-sm">, variar</span>
                                     {{-- Input de porcentagem --}}
                                     <input type="number" step="0.01" x-model="rule.percentage_change" class="w-20 text-xs bg-gray-700 border-gray-600 rounded focus:ring-amber-500 focus:border-amber-500" placeholder="%">
                                     <span class="text-sm">%</span>
                                     {{-- Campos hidden para enviar ao backend --}}
                                     <input type="hidden" :name="'rules[' + index + '][time]'" :value="rule.time">
                                     <input type="hidden" :name="'rules[' + index + '][percentage_change]'" :value="rule.percentage_change">
                                     <button type="button" @click="removeRule(index)" class="ml-auto text-red-500 hover:text-red-400 p-1 rounded-full hover:bg-gray-600">
                                         <x-heroicon-o-trash class="w-4 h-4"/>
                                     </button>
                                 </div>
                             </template>
                             <p x-show="formData.rules.length === 0" class="text-sm text-gray-500">Nenhuma regra adicionada.</p>
                         </div>
                         <x-button.secondary type="button" @click="addRule()" class="mt-2 text-xs">
                            <x-heroicon-o-plus class="w-3 h-3 mr-1"/> Adicionar Regra
                         </x-button.secondary>
                    </div>

                    {{-- Input hidden para enviar as regras como JSON (alternativa ou complemento) --}}
                    <input type="hidden" name="rules_json" :value="JSON.stringify(formData.rules)">
                    <div x-show="formErrors.length > 0" class="mt-2 text-xs text-red-400" x-text="formErrors.join(', ')"></div>
                </div>

                <div class="mt-6 text-right">
                    <x-button.secondary type="button" @click="$dispatch('close')">Cancelar</x-button.secondary>
                    <x-button.primary type="submit" class="ml-2" :disabled="loading">
                        <span x-show="!loading">Salvar Ativo</span>
                        <span x-show="loading">Salvando...</span> {{-- Feedback de loading --}}
                    </x-button.primary>
                </div>
            </form>
        </x-modal.form>
    </div>

    {{-- Tabela de Ativos --}}
    <x-card>
        <x-table :headers="['Logo', 'Nome', 'Símbolo', 'Tipo', 'Preço Atual (USD)', 'Variação (24h)', 'Regras Auto.', 'Ações']">
            @forelse ($assets as $asset)
                <tr class="hover:bg-gray-700" x-data="{ confirmDelete: false }">
                    <x-table.td>
                        @if ($asset->logo_url)
                            <img src="{{ $asset->logo_url }}" alt="{{ $asset->name }} Logo" class="w-8 h-8 rounded-full object-contain bg-white p-0.5"> {{-- object-contain e fundo branco ajudam na visualização --}}
                        @else
                            <div class="flex items-center justify-center w-8 h-8 bg-gray-600 rounded-full text-xs text-gray-300 font-semibold">
                                {{ substr($asset->symbol, 0, 1) }}
                            </div>
                        @endif
                    </x-table.td>
                    <x-table.td>{{ $asset->name }}</x-table.td>
                    <x-table.td>{{ $asset->symbol }}</x-table.td>
                    <x-table.td>{{ ucfirst($asset->type) }}</x-table.td>
                    <x-table.td>${{ number_format($asset->current_price, 2) }}</x-table.td>
                    <x-table.td>
                        @php $variation = $asset->variation_24h ?? 0; @endphp
                         <span class="{{ $variation >= 0 ? 'text-green-400' : 'text-red-400' }}">
                             {{ $variation >= 0 ? '+' : '' }}{{ number_format($variation, 2) }}%
                         </span>
                    </x-table.td>
                    <x-table.td>
                        <span class="text-sm text-gray-400">{{ $asset->rules_count ?? count($asset->rules ?? []) }}</span> {{-- Assumindo que o backend conta as regras --}}
                    </x-table.td>
                    <x-table.td>
                        <div class="flex space-x-2">
                            {{-- Botão Editar (usa Alpine para preencher modal) --}}
                            <button @click="editAssetData = {{ $asset->toJson() }}; showCreateModal = true"
                                    class="p-1 text-gray-400 hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-amber-500 rounded-full"
                                    title="Editar Ativo">
                                <x-heroicon-o-pencil-alt class="w-4 h-4"/>
                            </button>

                            {{-- Botão Excluir com confirmação --}}
                             <div class="inline-block">
                                 <button @click="confirmDelete = true" class="p-1 text-red-500 hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-amber-500 rounded-full" title="Excluir">
                                     <x-heroicon-o-trash class="w-4 h-4"/>
                                 </button>

                                 <x-modal.confirm show="confirmDelete" @close="confirmDelete = false" title="Confirmar Exclusão" message="Tem certeza que deseja excluir o ativo '{{ $asset->name }}'? Esta ação não pode ser desfeita.">
                                     <form method="POST" action="{{ route('admin.investments.destroy', $asset) }}" class="inline">
                                         @csrf
                                         @method('DELETE')
                                         <x-button.danger type="submit">Excluir Ativo</x-button.danger>
                                         <x-button.secondary type="button" @click="confirmDelete = false" class="ml-2">Cancelar</x-button.secondary>
                                     </form>
                                 </x-modal.confirm>
                            </div>
                        </div>
                    </x-table.td>
                </tr>
            @empty
                 <tr>
                    <x-table.td colspan="8" class="text-center">Nenhum ativo de investimento cadastrado.</x-table.td>
                 </tr>
            @endforelse
        </x-table>

         {{-- Paginação --}}
         <div class="p-4 bg-gray-800 border-t border-gray-700">
             {{ $assets->links() }}
         </div>
    </x-card>

    {{-- Script Alpine.js para o formulário do modal --}}
    <script>
        function assetForm(initialData = null) {
            return {
                formData: {
                    name: '',
                    symbol: '',
                    description: '',
                    type: 'stock',
                    current_price: '',
                    logo_url: '',
                    operating_fee: '',
                    rules: [], // { time: 'HH:MM', percentage_change: 'X.XX' }
                },
                loading: false,
                formErrors: [],
                editAssetData: initialData, // Para carregar dados na edição

                initializeForm(data) {
                    if (data) {
                        this.formData = {
                            name: data.name || '',
                            symbol: data.symbol || '',
                            description: data.description || '',
                            type: data.type || 'stock',
                            current_price: data.current_price || '',
                            logo_url: data.logo_url || '',
                            operating_fee: data.operating_fee || '',
                            // As regras precisam ser passadas como array no JSON do $asset->toJson()
                            rules: Array.isArray(data.rules) ? data.rules.map(r => ({ time: r.time, percentage_change: r.percentage_change })) : [],
                        };
                    } else {
                         this.formData = { name: '', symbol: '', description: '', type: 'stock', current_price: '', logo_url: '', operating_fee: '', rules: [] };
                    }
                     this.formErrors = []; // Limpa erros ao abrir/mudar
                },

                addRule() {
                    this.formData.rules.push({ time: '', percentage_change: '' });
                },

                removeRule(index) {
                    this.formData.rules.splice(index, 1);
                },

                async submitForm(actionUrl, method) {
                    this.loading = true;
                    this.formErrors = [];
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    // Prepara o corpo do formulário
                    // Usar FormData se precisar de upload de arquivos, senão JSON funciona
                    const payload = { ...this.formData };
                    payload._token = csrfToken; // Adiciona CSRF
                    if (method === 'PUT') {
                        payload._method = 'PUT'; // Adiciona method spoofing para PUT
                    }

                    try {
                        const response = await fetch(actionUrl, {
                            method: 'POST', // Sempre POST, mas _method define a ação real
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken // Header CSRF também
                            },
                            body: JSON.stringify(payload) // Envia como JSON
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            if (response.status === 422 && result.errors) {
                                // Captura erros de validação
                                this.formErrors = Object.values(result.errors).flat();
                            } else {
                                throw new Error(result.message || 'Erro ao salvar o ativo.');
                            }
                        } else {
                             // Sucesso - fechar modal e recarregar ou atualizar a tabela dinamicamente
                            this.$dispatch('close'); // Fecha o modal
                            location.reload(); // Recarrega a página (mais simples)
                             // Ou poderia emitir um evento para a tabela atualizar via Alpine/Livewire
                        }

                    } catch (error) {
                         this.formErrors = [error.message];
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection

{{-- Placeholders para Componentes (Exemplo) --}}
{{-- resources/views/components/modal/form.blade.php (Similar ao modal.confirm mas com mais espaço e sem ícone de alerta padrão) --}}
{{-- @props(['show' => false, 'title'])
<div x-show="{{ $show }}" style="display: none;" ... (resto similar ao modal.confirm)>
    <div class="inline-block w-full max-w-2xl p-6 my-8 ... bg-gray-800 rounded-lg shadow-xl ...">
        <div class="flex items-center justify-between pb-3 border-b border-gray-700">
             <h3 class="text-lg font-medium text-gray-100">{{ $title }}</h3>
             <button @click="$dispatch('close')" class="text-gray-400 hover:text-gray-300">
                 <x-heroicon-o-x class="w-5 h-5"/>
             </button>
        </div>
        <div class="mt-4">
             {{ $slot }} {{-- O formulário entra aqui --}}
        {{-- </div>
    </div>
</div> --}}