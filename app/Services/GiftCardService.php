<?php
namespace App\Services;

use App\Models\User;
use App\Models\GiftCard;
use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification; // Para enviar email
use App\Services\AuditLogService;
use App\Exceptions\InsufficientBalanceException; // Criar
use App\Exceptions\GiftCardException; // Criar
// use App\Notifications\GiftCardReceived; // Criar

class GiftCardService
{
     public function __construct(
         private readonly AuditLogService $auditLogService,
         private readonly CurrencyConversionService $conversionService // Para verificar pagamento
         ) {}

    /**
     * Retorna dados para a página de Gift Cards do usuário.
     */
    public function getGiftCardPageData(User $user): array
    {
        Log::debug("Getting gift card page data", ['user_id' => $user->id]);
        // TODO: Buscar wallets com saldo para o formulário de compra
        $userWallets = $user->wallets()->where('is_active', true)->where('balance', '>', 0)->get();

        // TODO: Buscar GCs do usuário e enviados
        $myGiftCards = GiftCard::where(fn($q) => $q->where('recipient_email', $user->email)->orWhere('purchased_by_user_id', $user->id)->whereNull('recipient_email'))
                              ->orWhere('redeemed_by_user_id', $user->id)
                              ->latest()->get(); // Adicionar paginação se necessário

        $sentGiftCards = GiftCard::where('purchased_by_user_id', $user->id)
                                ->whereNotNull('recipient_email')
                                ->latest()->get(); // Adicionar paginação

        return compact('user', 'userWallets', 'myGiftCards', 'sentGiftCards');
    }

    /**
     * Cria e processa a compra de um Gift Card.
     * @throws InsufficientBalanceException | GiftCardException | \Exception
     */
    // public function purchaseGiftCard(User $purchaser, array $validatedData): GiftCard
    // {
    //     $currency = $validatedData['currency'];
    //     $amount = (float) $validatedData['amount'];
    //     $paymentWalletId = $validatedData['payment_wallet_id'];
    //     $recipientEmail = $validatedData['recipient_email'] ?? null;
    //     $message = $validatedData['message'] ?? null;
    //     Log::info("Purchasing gift card", ['user_id' => $purchaser->id, 'data' => $validatedData]);
    //
    //     // TODO: Implementar lógica completa:
    //     // 1. Verificar senha de transação (se aplicável).
    //     // 2. Buscar carteira de pagamento com lockForUpdate.
    //     // 3. Verificar se moeda da carteira == moeda do GC ou calcular valor necessário na moeda da carteira usando conversionService.
    //     // 4. Verificar saldo. Lançar InsufficientBalanceException.
    //     // 5. Iniciar DB::transaction.
    //     // 6. Debitar valor da carteira (bcsub).
    //     // 7. Criar Transaction 'gift_card_purchase'.
    //     // 8. Criar o GiftCard (Model gera o código).
    //     // 9. Logar auditoria.
    //     // 10. DB::commit.
    //     // 11. Se houver recipientEmail, enviar notificação/email.
    //     //     Notification::route('mail', $recipientEmail)->notify(new GiftCardReceived($newGiftCard, $purchaser, $message));
    //     // 12. Capturar erros, rollback, logar.
    //     // 13. Retornar o GiftCard criado.
    //
    //     return new GiftCard(); // Placeholder
    // }

    /**
     * Resgata um Gift Card para a carteira do usuário.
     * @throws GiftCardException | WalletException | \Exception
     */
    // public function redeemGiftCard(User $redeemer, string $code): GiftCard
    // {
    //     $code = strtoupper(str_replace('-', '', $code));
    //     Log::info("Redeeming gift card", ['user_id' => $redeemer->id, 'code' => $code]);
    //
    //     // TODO: Implementar lógica completa:
    //     // 1. Buscar GiftCard pelo código com lockForUpdate.
    //     // 2. Verificar se existe e se pode ser resgatado (canBeRedeemed()). Lançar GiftCardException.
    //     // 3. Iniciar DB::transaction.
    //     // 4. Encontrar ou criar a Wallet do usuário na moeda do GiftCard (lockForUpdate).
    //     // 5. Creditar o saldo do GiftCard na Wallet (bcadd).
    //     // 6. Atualizar GiftCard (status='redeemed', balance=0, redeemed_by_user_id, redeemed_at).
    //     // 7. Criar Transaction 'gift_card_redeem'.
    //     // 8. Logar auditoria.
    //     // 9. DB::commit.
    //     // 10. Capturar erros, rollback, logar.
    //     // 11. Retornar o GiftCard atualizado.
    //
    //     return new GiftCard(); // Placeholder
    // }
}