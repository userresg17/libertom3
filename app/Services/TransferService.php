<?php
namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\AuditLogService;
use App\Services\CurrencyConversionService;
use App\Exceptions\InsufficientBalanceException;
use App\Exceptions\InvalidQuoteException; // Criar
use App\Exceptions\TransferException; // Criar
// use App\Services\Gateways\SwiftService; // Placeholder
// use App\Services\Gateways\StripeConnectService; // Placeholder

class TransferService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly CurrencyConversionService $conversionService
        // private readonly SwiftService $swiftService, // Exemplo
        // private readonly StripeConnectService $stripeConnectService // Exemplo
        ) {}

    /**
     * Retorna dados necessários para o formulário de envio de dinheiro.
     */
    public function getSendMoneyFormData(User $user): array
    {
        Log::debug("Getting send money form data", ['user_id' => $user->id]);
        $user->loadMissing('wallets');
        $userWallets = $user->wallets()->where('balance', '>', 0)->get();

        // TODO: Buscar países e moedas suportados (de config/BD)
        $supportedCountries = config('libertom.supported_countries', []);
        $supportedCurrencies = config('libertom.supported_currencies', []);

        return compact('user', 'userWallets', 'supportedCountries', 'supportedCurrencies');
    }

    /**
     * Inicia uma transferência internacional ou doméstica.
     * @throws InsufficientBalanceException | InvalidQuoteException | TransferException | \Exception
     */
    // public function initiateTransfer(User $user, array $validatedData): Transaction
    // {
    //     Log::info("Initiating transfer", ['user_id' => $user->id, 'data' => $validatedData]);
    //
    //     // TODO: Implementar lógica completa:
    //     // 1. Verificar senha de transação.
    //     // 2. Validar a cotação (quote_id) usando CurrencyConversionService->validateQuote(). Lançar InvalidQuoteException.
    //     // 3. Buscar carteira de origem com lockForUpdate.
    //     // 4. Verificar saldo vs valor total debitado da cotação. Lançar InsufficientBalanceException.
    //     // 5. Iniciar DB::transaction.
    //     // 6. Debitar valor total da carteira (bcsub).
    //     // 7. Criar Transaction 'transfer_out' com status 'processing' e detalhes (recipient, cotação usada) em metadata.
    //     // 8. Chamar o Gateway/Serviço apropriado para iniciar a transferência externa (Swift, Stripe Connect, Cora TED/PIX, etc.) baseado no país/moeda/método implícito nos detalhes do destinatário.
    //     // 9. Se falha no gateway, rollback e lançar TransferException.
    //     // 10. Se sucesso, salvar referência do gateway na Transaction.
    //     // 11. Logar auditoria.
    //     // 12. DB::commit.
    //     // 13. Retornar a Transaction criada.
    //     // NOTA: Status final pode ser atualizado via webhook.
    //
    //     return new Transaction(); // Placeholder
    // }

    // --- Métodos para Webhooks de Gateways ---
    /** Atualiza status de transferência após notificação do gateway. */
    // public function handleTransferStatusUpdate(string $transactionReference, string $newStatus, ?string $reason = null): void { /* ... Busca Transaction pela ref, atualiza status, credita de volta se falhou ... */ }

}