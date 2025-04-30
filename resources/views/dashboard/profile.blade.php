<x-app-layout>
    <x-slot name="header">
        {{ __('Meu Perfil e Segurança') }}
    </x-slot>

    <div x-data="{
        tab: window.location.hash ? window.location.hash.substring(1) : 'personal',
        kycFiles: { selfie: null, doc_front: null, doc_back: null },
        kycPreviews: { selfie: '{{ $user->kycDocument('selfie')?->temporaryUrl() ?? '' }}', doc_front: '{{ $user->kycDocument('doc_front')?->temporaryUrl() ?? '' }}', doc_back: '{{ $user->kycDocument('doc_back')?->temporaryUrl() ?? '' }}' },
        handleFileUpload(event, type) {
            const file = event.target.files[0];
            if (file) {
                this.kycFiles[type] = file;
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.kycPreviews[type] = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                // Handle file removal if needed - might need original URL from backend
                this.kycFiles[type] = null;
                this.kycPreviews[type] = '{{-- Logic to get original preview URL for the specific type --}}';
            }
        }
    }" class="max-w-4xl mx-auto"> {{-- Limit width for better readability on large screens --}}

         {{-- Navegação das Abas --}}
        <div class="mb-6 border-b border-gray-700">
            <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                <a href="#personal" @click="tab = 'personal'"
                   :class="{ 'border-amber-500 text-amber-400': tab === 'personal', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'personal' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Informações Pessoais
                </a>
                <a href="#security" @click="tab = 'security'"
                   :class="{ 'border-amber-500 text-amber-400': tab === 'security', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'security' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Segurança
                </a>
                <a href="#kyc" @click="tab = 'kyc'"
                   :class="{ 'border-amber-500 text-amber-400': tab === 'kyc', 'border-transparent text-gray-400 hover:text-gray-200 hover:border-gray-500': tab !== 'kyc' }"
                   class="px-1 py-4 text-sm font-medium whitespace-nowrap border-b-2 focus:outline-none">
                   Verificação (KYC)
                </a>
            </nav>
        </div>

        {{-- Conteúdo das Abas --}}
        <div class="space-y-6">
            {{-- Aba: Informações Pessoais --}}
            <div x-show="tab === 'personal'" x-cloak>
                <x-card>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('patch') {{-- Laravel standard for profile update --}}
                        <div class="p-6 space-y-4">
                            <h3 class="text-lg font-medium text-gray-100">Seus Dados</h3>
                             <div>
                                <x-input.label for="name" value="Nome Completo" />
                                <x-input.text id="name" name="name" type="text" class="block w-full mt-1" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input.error :messages="$errors->updateProfileInformation->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input.label for="email" value="Email" />
                                <x-input.text id="email" name="email" type="email" class="block w-full mt-1" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input.error :messages="$errors->updateProfileInformation->get('email')" class="mt-2" />

                                {{-- Email Verification Status --}}
                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-2 text-sm text-yellow-400">
                                        Seu endereço de e-mail não foi verificado.
                                         <button form="send-verification" class="underline hover:text-yellow-300">
                                             Clique aqui para reenviar o e-mail de verificação.
                                         </button>
                                     </div>
                                     @if (session('status') === 'verification-link-sent')
                                         <p class="mt-2 text-sm font-medium text-green-400">
                                            Um novo link de verificação foi enviado para seu endereço de e-mail.
                                         </p>
                                     @endif
                                @endif
                            </div>
                            {{-- Adicionar outros campos: telefone, endereço, data de nascimento, etc. se coletados --}}
                        </div>
                         <div class="flex items-center justify-end px-6 py-4 bg-gray-750">
                             <x-button.primary type="submit">Salvar Alterações</x-button.primary>
                         </div>
                    </form>
                </x-card>
                 {{-- Form separado para reenviar verificação de email --}}
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
                        @csrf
                    </form>
                 @endif
            </div>

            {{-- Aba: Segurança --}}
            <div x-show="tab === 'security'" x-cloak>
                {{-- Alterar Senha de Login --}}
                <x-card class="mb-6">
                     <form method="POST" action="{{ route('password.update') }}">
                         @csrf
                         @method('put')
                         <div class="p-6 space-y-4">
                             <h3 class="text-lg font-medium text-gray-100">Alterar Senha de Login</h3>
                             <div>
                                 <x-input.label for="current_password" value="Senha Atual" />
                                 <x-input.text id="current_password" name="current_password" type="password" class="block w-full mt-1" autocomplete="current-password" required/>
                                 <x-input.error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                             </div>
                             <div>
                                 <x-input.label for="password" value="Nova Senha" />
                                 <x-input.text id="password" name="password" type="password" class="block w-full mt-1" autocomplete="new-password" required/>
                                 <x-input.error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                             </div>
                              <div>
                                 <x-input.label for="password_confirmation" value="Confirmar Nova Senha" />
                                 <x-input.text id="password_confirmation" name="password_confirmation" type="password" class="block w-full mt-1" autocomplete="new-password" required/>
                                 <x-input.error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                             </div>
                         </div>
                         <div class="flex items-center justify-end px-6 py-4 bg-gray-750">
                             <x-button.primary type="submit">Alterar Senha</x-button.primary>
                              @if (session('status') === 'password-updated')
                                 <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                    class="ml-3 text-sm text-green-400">Salvo.</p>
                             @endif
                         </div>
                     </form>
                </x-card>

                {{-- Senha de Transação --}}
                <x-card>
                     {{-- Formulário para DEFINIR ou ALTERAR senha de transação --}}
                     {{-- A lógica exata dependerá se o usuário já tem uma senha de transação --}}
                     <form method="POST" action="{{ route('profile.transactionPassword.update') }}"> {{-- Rota de exemplo --}}
                         @csrf
                         @method('put')
                         <div class="p-6 space-y-4">
                             <h3 class="text-lg font-medium text-gray-100">Senha de Transação</h3>
                             <p class="text-sm text-gray-400">Esta senha será solicitada para confirmar operações financeiras importantes.</p>

                              @if ($user->hasTransactionPassword()) {{-- Lógica do Model User --}}
                                 <div>
                                     <x-input.label for="current_transaction_password" value="Senha de Transação Atual" />
                                     <x-input.text id="current_transaction_password" name="current_transaction_password" type="password" class="block w-full mt-1" required/>
                                     <x-input.error :messages="$errors->updateTransactionPassword->get('current_transaction_password')" class="mt-2" />
                                 </div>
                                 <div>
                                     <x-input.label for="new_transaction_password" value="Nova Senha de Transação" />
                                     <x-input.text id="new_transaction_password" name="transaction_password" type="password" class="block w-full mt-1" required/>
                                     <x-input.error :messages="$errors->updateTransactionPassword->get('transaction_password')" class="mt-2" />
                                 </div>
                                  <div>
                                     <x-input.label for="transaction_password_confirmation" value="Confirmar Nova Senha de Transação" />
                                     <x-input.text id="transaction_password_confirmation" name="transaction_password_confirmation" type="password" class="block w-full mt-1" required/>
                                     <x-input.error :messages="$errors->updateTransactionPassword->get('transaction_password_confirmation')" class="mt-2" />
                                 </div>
                              @else
                                 <div>
                                     <x-input.label for="transaction_password" value="Definir Senha de Transação" />
                                     <x-input.text id="transaction_password" name="transaction_password" type="password" class="block w-full mt-1" required/>
                                      <x-input.error :messages="$errors->updateTransactionPassword->get('transaction_password')" class="mt-2" />
                                 </div>
                                 <div>
                                     <x-input.label for="transaction_password_confirmation" value="Confirmar Senha de Transação" />
                                     <x-input.text id="transaction_password_confirmation" name="transaction_password_confirmation" type="password" class="block w-full mt-1" required/>
                                      <x-input.error :messages="$errors->updateTransactionPassword->get('transaction_password_confirmation')" class="mt-2" />
                                 </div>
                              @endif
                         </div>
                         <div class="flex items-center justify-end px-6 py-4 bg-gray-750">
                            <x-button.primary type="submit">
                                {{ $user->hasTransactionPassword() ? 'Alterar Senha de Transação' : 'Definir Senha de Transação' }}
                            </x-button.primary>
                            @if (session('status') === 'transaction-password-updated')
                                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                   class="ml-3 text-sm text-green-400">Salvo.</p>
                            @endif
                         </div>
                     </form>
                </x-card>

                {{-- Autenticação de Dois Fatores (2FA) - Placeholder --}}
                {{-- <x-card class="mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-100">Autenticação de Dois Fatores (2FA)</h3>
                        <p class="mt-2 text-sm text-gray-400">Adicione uma camada extra de segurança à sua conta.</p>
                        <div class="mt-4">
                            Placeholder para botões de Habilitar/Desabilitar 2FA (via Fortify ou similar)
                            <x-button.secondary disabled>Configurar 2FA (Em breve)</x-button.secondary>
                        </div>
                    </div>
                </x-card> --}}
            </div>

            {{-- Aba: Verificação (KYC) --}}
            <div x-show="tab === 'kyc'" x-cloak id="kyc">
                 <x-card>
                     <div class="p-6">
                         <h3 class="mb-2 text-lg font-medium text-gray-100">Verificação de Identidade (KYC)</h3>
                         <p class="mb-4 text-sm text-gray-400">Status Atual:
                              <x-badge :color="$user->kyc_status_color"> {{-- Use um Accessor no Model User para a cor --}}
                                 {{ $user->kyc_status_label }} {{-- Use um Accessor no Model User para o label --}}
                              </x-badge>
                         </p>

                         @if($user->kyc_status == 'rejected' || $user->kyc_status == 'resubmission_requested')
                             <div class="p-4 mb-4 text-sm text-yellow-300 bg-yellow-800 border border-yellow-700 rounded-lg">
                                 <strong>Motivo:</strong> {{ $user->kyc_rejection_reason ?? 'Verifique seu e-mail para mais detalhes ou contate o suporte.' }}
                             </div>
                         @endif

                         @if ($user->kyc_status != 'approved')
                         <form method="POST" action="{{ route('profile.kyc.submit') }}" enctype="multipart/form-data">
                             @csrf
                             <p class="mb-4 text-sm text-gray-400">Para ativar todas as funcionalidades, precisamos verificar sua identidade. Por favor, envie os seguintes documentos:</p>

                             <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                 {{-- Upload Selfie --}}
                                 <div class="space-y-1 text-center">
                                     <label for="kyc_selfie" class="block text-sm font-medium text-gray-300">Sua Selfie Segurando o Documento</label>
                                     <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-600 border-dashed rounded-md hover:border-amber-500">
                                         <div class="space-y-1 text-center">
                                             <img x-show="kycPreviews.selfie" :src="kycPreviews.selfie" class="object-contain w-auto h-24 mx-auto mb-2 rounded">
                                             <x-heroicon-o-camera x-show="!kycPreviews.selfie" class="w-12 h-12 mx-auto text-gray-500"/>
                                             <div class="flex text-sm text-gray-500">
                                                 <label for="kyc_selfie" class="relative font-medium text-amber-400 bg-gray-800 rounded-md cursor-pointer hover:text-amber-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-amber-500 focus-within:ring-offset-gray-800">
                                                     <span>Carregar arquivo</span>
                                                     <input id="kyc_selfie" name="selfie" type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="handleFileUpload(event, 'selfie')">
                                                 </label>
                                                 <p class="pl-1" x-text="kycFiles.selfie ? kycFiles.selfie.name : 'ou arraste e solte'"></p>
                                             </div>
                                             <p class="text-xs text-gray-500">PNG, JPG, WEBP até 5MB</p>
                                         </div>
                                     </div>
                                     <x-input.error :messages="$errors->kyc->get('selfie')" class="mt-2" />
                                 </div>

                                 {{-- Upload Documento (Frente) --}}
                                 <div class="space-y-1 text-center">
                                      <label for="kyc_doc_front" class="block text-sm font-medium text-gray-300">Frente do Documento (RG/CNH)</label>
                                      {{-- Estrutura similar ao upload de selfie --}}
                                      <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-600 border-dashed rounded-md hover:border-amber-500">
                                          <div class="space-y-1 text-center">
                                              <img x-show="kycPreviews.doc_front" :src="kycPreviews.doc_front" class="object-contain w-auto h-24 mx-auto mb-2 rounded">
                                              <x-heroicon-o-document-text x-show="!kycPreviews.doc_front" class="w-12 h-12 mx-auto text-gray-500"/>
                                              <div class="flex text-sm text-gray-500">
                                                   <label for="kyc_doc_front" class="relative font-medium text-amber-400 bg-gray-800 rounded-md cursor-pointer hover:text-amber-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-amber-500 focus-within:ring-offset-gray-800">
                                                      <span>Carregar arquivo</span>
                                                      <input id="kyc_doc_front" name="doc_front" type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="handleFileUpload(event, 'doc_front')">
                                                  </label>
                                                  <p class="pl-1" x-text="kycFiles.doc_front ? kycFiles.doc_front.name : 'ou arraste e solte'"></p>
                                              </div>
                                              <p class="text-xs text-gray-500">PNG, JPG, WEBP até 5MB</p>
                                          </div>
                                      </div>
                                      <x-input.error :messages="$errors->kyc->get('doc_front')" class="mt-2" />
                                 </div>

                                 {{-- Upload Documento (Verso) --}}
                                 <div class="space-y-1 text-center">
                                       <label for="kyc_doc_back" class="block text-sm font-medium text-gray-300">Verso do Documento (RG/CNH)</label>
                                       {{-- Estrutura similar ao upload de selfie --}}
                                      <div class="flex justify-center px-6 pt-5 pb-6 mt-1 border-2 border-gray-600 border-dashed rounded-md hover:border-amber-500">
                                           <div class="space-y-1 text-center">
                                               <img x-show="kycPreviews.doc_back" :src="kycPreviews.doc_back" class="object-contain w-auto h-24 mx-auto mb-2 rounded">
                                               <x-heroicon-o-document-text x-show="!kycPreviews.doc_back" class="w-12 h-12 mx-auto text-gray-500"/>
                                               <div class="flex text-sm text-gray-500">
                                                    <label for="kyc_doc_back" class="relative font-medium text-amber-400 bg-gray-800 rounded-md cursor-pointer hover:text-amber-300 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-amber-500 focus-within:ring-offset-gray-800">
                                                       <span>Carregar arquivo</span>
                                                       <input id="kyc_doc_back" name="doc_back" type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="handleFileUpload(event, 'doc_back')">
                                                   </label>
                                                   <p class="pl-1" x-text="kycFiles.doc_back ? kycFiles.doc_back.name : 'ou arraste e solte'"></p>
                                               </div>
                                               <p class="text-xs text-gray-500">PNG, JPG, WEBP até 5MB</p>
                                           </div>
                                       </div>
                                       <x-input.error :messages="$errors->kyc->get('doc_back')" class="mt-2" />
                                 </div>
                             </div>

                             <div class="mt-6 text-right">
                                 <x-button.primary type="submit" :disabled="$user->kyc_status === 'pending'">
                                     {{ $user->kyc_status === 'pending' ? 'Em Análise...' : 'Enviar Documentos para Verificação' }}
                                 </x-button.primary>
                             </div>
                         </form>
                         @else
                             <p class="text-sm text-green-400">Sua identidade foi verificada com sucesso!</p>
                              {{-- Mostrar thumbs dos documentos aprovados --}}
                              <div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-3">
                                  @foreach($user->kycDocuments as $doc)
                                      <div class="p-2 border border-gray-700 rounded bg-gray-750">
                                           <p class="mb-1 text-xs font-medium text-gray-300 capitalize">{{ str_replace('_', ' ', $doc->type) }}</p>
                                           <img src="{{ $doc->temporaryUrl() }}" alt="Documento {{ $doc->type }}" class="object-contain w-full rounded h-28">
                                      </div>
                                  @endforeach
                              </div>
                         @endif
                     </div>
                 </x-card>
            </div>
        </div>
    </div>
</x-app-layout>