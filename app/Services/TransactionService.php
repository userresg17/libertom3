<?php
namespace App\Services\Admin;

use App\Models\Transaction;
use App\Models\AdminUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogService;
use App\Exceptions\TransactionException; // Criar

class TransactionService
{
     public function __construct(
         private readonly AuditLogService $auditLogService
         // private readonly WithdrawalProcessingService $withdrawalService // Exemplo
         ) {}

    /**
     * Lista transações com filtros e paginação para o admin.
     */
    public function listTransactions(array $filters): LengthAwarePaginator
    {
        Log::debug('Admin: Listing transactions', $filters);
        $query = Transaction::with(['user', 'asset', 'wallet']);
         // TODO: Implementar filtros (user_search, type, status, date_from, date_to)
         return $query->latest()->paginate(config('libertom.pagination.admin_transactions', 25))->withQueryString();
    }

    /**
     * Busca detalhes de uma transação.
     */
    public function getTransactionDetails(Transaction $transaction): Transaction
    {
        Log::debug('Admin: Getting transaction details', ['transaction_id' => $transaction->id]);
         // TODO: Carregar mais relações se necessário
        return $transaction->loadMissing(['user', 'asset', 'wallet', 'relatedTransaction']);
    }

    /**
     * Aprova um saque pendente.
     * @throws TransactionException Se não puder aprovar.
     */
    public function approveWithdrawal(Transaction $transaction, AdminUser $adminUser): array
    {
        Log::info('Admin: Approving withdrawal', ['transaction_id' => $transaction->id, 'admin_id' => $adminUser->id]);
        if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
             throw new TransactionException('Status inválido para aprovação.');
        }

        // TODO: Implementar lógica completa:
        // 1. Iniciar DB::transaction.
        // 2. Chamar serviço/gateway real para processar o saque externo.
        // 3. Se sucesso no gateway:
        //    a. Atualizar status da transação para 'processing' ou 'completed'.
        //    b. Salvar referência do gateway em metadata.
        //    c. Logar auditoria.
        //    d. DB::commit.
        //    e. Retornar ['success' => true].
        // 4. Se falha no gateway ou outra exceção:
        //    a. DB::rollBack.
        //    b. Atualizar status da transação para 'failed' com motivo.
        //    c. Logar erro e auditoria de falha.
        //    d. Lançar TransactionException ou retornar ['success' => false, 'message' => ...].

        // Simulação
        $transaction->update(['status' => 'processing']);
        $this->auditLogService->log('withdrawal_approved', $transaction, [], $adminUser);
        return ['success' => true, 'message' => 'Saque encaminhado para processamento.'];

    }

    /**
     * Rejeita um saque pendente.
     * @throws TransactionException Se não puder rejeitar.
     */
    public function rejectWithdrawal(Transaction $transaction, string $reason, AdminUser $adminUser): array
    {
         Log->info('Admin: Rejecting withdrawal', ['transaction_id' => $transaction->id, 'admin_id' => $adminUser->id, 'reason' => $reason]);
         if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
              throw new TransactionException('Status inválido para rejeição.');
         }

        // TODO: Implementar lógica completa:
        // 1. Iniciar DB::transaction.
        // 2. Estornar fundos para a carteira do usuário se foram reservados (lockForUpdate, bcadd).
        // 3. Atualizar status da transação para 'rejected'.
        // 4. Salvar motivo da rejeição em metadata.
        // 5. Logar auditoria.
        // 6. DB::commit.
        // 7. Retornar ['success' => true].
        // 8. Capturar erros, rollback, logar, lançar exceção ou retornar ['success' => false].

         // Simulação
         $transaction->update(['status' => 'rejected', 'metadata' => array_merge($transaction->metadata ?? [], ['rejection_reason' => $reason, 'rejected_by' => $adminUser->id])]);
         $this->auditLogService->log('withdrawal_rejected', $transaction, ['reason' => $reason], $adminUser);
         return ['success' => true, 'message' => 'Saque rejeitado.'];
    }
}