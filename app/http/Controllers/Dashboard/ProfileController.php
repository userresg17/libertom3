<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ProfileUpdateRequest; // Vem com Breeze, ou criar um customizado
use App\Http\Requests\KycSubmitRequest; // <-- Criar
use App\Http\Requests\UpdateTransactionPasswordRequest; // <-- Criar
use Illuminate\Support\Facades\Hash; // Para senha de transação
use Illuminate\Validation\Rules\Password; // Para regras de senha
use Illuminate\Support\Facades\Storage; // Para upload KYC
// TODO: Importar KycService, AuditLogService

class ProfileController extends Controller
{
    /**
     * Exibe a página de perfil e configurações.
     */
    public function edit(Request $request): View
    {
         $user = $request->user()->loadMissing(['kycDocuments']); // Carrega documentos KYC
         return view('dashboard.profile', [ // Usando a view profile.blade.php que criamos
             'user' => $user,
         ]);
    }

    /**
     * Atualiza as informações do perfil (nome, email, etc.).
     * Baseado no ProfileController do Breeze, mas pode ser adaptado.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Preenche os dados básicos validados
        $user->fill($validated);

        // Se o email foi alterado, reseta a verificação
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // TODO: Logar alteração de perfil

        // Se o email foi alterado, pode reenviar a verificação automaticamente
        // if ($user->wasChanged('email') && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
        //     $user->sendEmailVerificationNotification();
        // }

        return Redirect::route('profile.edit', ['#personal'])->with('success', 'Perfil atualizado.'); // Leva para a tab correta
    }

    /**
     * Processa o upload de documentos KYC.
     */
    public function submitKyc(KycSubmitRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Verificar se o usuário pode enviar (status não 'approved' ou 'pending')
        if (in_array($user->kyc_status, ['approved', 'pending'])) {
            return back()->with('error', 'Seu KYC já foi aprovado ou está em análise.');
        }

        $validated = $request->validated(); // Contém 'selfie', 'doc_front', 'doc_back' como UploadedFile

        // TODO: Chamar KycService para processar os arquivos
        // Exemplo básico de armazenamento (KycService faria isso melhor)
        try {
             $basePath = "kyc/user_{$user->id}";
             $paths = [];
             foreach(['selfie', 'doc_front', 'doc_back'] as $type) {
                 if ($request->hasFile($type)) {
                     // Deletar documento antigo do mesmo tipo, se existir
                     $oldDoc = $user->kycDocument($type);
                     if ($oldDoc && $oldDoc->file_path && Storage::exists($oldDoc->file_path)) {
                         Storage::delete($oldDoc->file_path);
                         $oldDoc->delete(); // Remove o registro do BD também
                     }

                     $path = $request->file($type)->store($basePath, 'public'); // Usar disco 's3' ou 'private' em produção
                      if (!$path) {
                          throw new \Exception("Falha ao salvar o arquivo: $type");
                      }
                     // Registrar no banco de dados
                     $user->kycDocuments()->create([
                         'type' => $type,
                         'file_path' => $path,
                         'status' => 'uploaded',
                     ]);
                     $paths[$type] = $path;
                 }
             }

             // Atualizar status do usuário e limpar razão de rejeição
             $user->update([
                 'kyc_status' => 'pending',
                 'kyc_rejection_reason' => null,
             ]);

            // TODO: Logar submissão KYC

            return Redirect::route('profile.edit', ['#kyc'])->with('success', 'Documentos enviados para análise.');

        } catch (\Exception $e) {
             Log::error("Erro no upload de KYC para user ID {$user->id}: " . $e->getMessage());
             // TODO: Deletar arquivos já salvos em caso de erro parcial
             if (!empty($paths)) { Storage::delete(array_values($paths)); }
             return back()->with('error', 'Falha ao enviar documentos. Tente novamente.');
        }
    }

    /**
     * Define ou atualiza a senha de transação.
     */
    public function updateTransactionPassword(UpdateTransactionPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Se o usuário já tem senha, verifica a atual
        if ($user->hasTransactionPassword()) {
             if (! $user->verifyTransactionPassword($validated['current_transaction_password'])) {
                 // Usando withErrors para exibir erro no campo específico
                 return back()->withErrors(['current_transaction_password' => 'A senha de transação atual está incorreta.'])->withInput();
             }
        }

        // Atualiza (ou define) a nova senha (o mutator no Model User cuida do hash)
        $user->forceFill([
            'transaction_password' => $validated['transaction_password'],
        ])->save();

         // TODO: Logar definição/alteração de senha de transação

        return Redirect::route('profile.edit', ['#security'])->with('success', 'Senha de transação definida/atualizada com sucesso.');
    }


    /**
     * Excluir conta do usuário (Soft Delete).
     * Geralmente vem do Breeze/Fortify, mas pode ser customizado aqui.
     */
    // public function destroy(Request $request): RedirectResponse { ... }

}