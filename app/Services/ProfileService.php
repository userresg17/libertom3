<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // Para erros de senha
use App\Services\AuditLogService;

class ProfileService
{
     public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Atualiza os detalhes básicos do perfil do usuário.
     */
    public function updateProfileDetails(User $user, array $validatedData): User
    {
        Log::info("Updating profile details", ['user_id' => $user->id]);
        // $oldData = $user->only(array_keys($validatedData));

        // Lógica para verificar se email mudou e resetar verificação
        $emailChanged = isset($validatedData['email']) && $validatedData['email'] !== $user->email;
        if ($emailChanged) {
            $validatedData['email_verified_at'] = null;
        }

        $user->update($validatedData);

        // Logar auditoria (ação do próprio usuário)
        $this->auditLogService->log('profile_updated', $user, ['changed_fields' => array_keys($validatedData)], $user);

        // Reenviar email de verificação se necessário
        // if ($emailChanged && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
        //    $user->sendEmailVerificationNotification();
        //    Log::info("Sent email verification to user", ['user_id' => $user->id]);
        // }

        return $user->refresh();
    }

    /**
     * Atualiza a senha de login do usuário.
     * (Se não usar o controller padrão do Breeze/Fortify)
     * @throws ValidationException
     */
    // public function updateLoginPassword(User $user, array $validatedData): void
    // {
    //     Log::info("Updating login password", ['user_id' => $user->id]);
    //     // 1. Verificar senha atual $validatedData['current_password'] usando Hash::check()
    //     // 2. Se inválida, lançar ValidationException::withMessages(['current_password' => ...])
    //     // 3. Se válida, atualizar $user->password = Hash::make($validatedData['password'])
    //     // 4. Salvar usuário
    //     // 5. Logar auditoria (sem logar senhas)
    //     // $this->auditLogService->log('login_password_updated', $user, [], $user);
    // }

    /**
     * Atualiza ou define a senha de transação do usuário.
     * @throws ValidationException
     */
    public function updateTransactionPassword(User $user, array $validatedData): void
    {
        Log::info("Updating transaction password", ['user_id' => $user->id]);
        $hasCurrent = $user->hasTransactionPassword();

        // 1. Se $hasCurrent, verificar $validatedData['current_transaction_password']
        if ($hasCurrent) {
             if (! $user->verifyTransactionPassword($validatedData['current_transaction_password'])) {
                 throw ValidationException::withMessages(['current_transaction_password' => __('A senha de transação atual está incorreta.')]);
             }
        }

        // 2. Atualizar a senha (o mutator no Model User cuida do hash)
        $user->forceFill([
            'transaction_password' => $validatedData['transaction_password'],
        ])->save();

        // 3. Logar auditoria
        $action = $hasCurrent ? 'transaction_password_updated' : 'transaction_password_set';
        $this->auditLogService->log($action, $user, [], $user);
    }
}