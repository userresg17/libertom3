<?php
namespace App\Services; // Ou App\Services\Dashboard

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Log;
use App\Services\CurrencyConversionService; // Service comum
use App\Services\AuditLogService; // Service comum
// use App\Services\Gateways\CoraService; // Exemplo de Gateway
// use App\Services\Gateways\StripeService; // Exemplo de Gateway
// use App\Services\Util\IbanGenerator; // Exemplo de gerador

class WalletService
{
     public function __construct(
        private readonly CurrencyConversionService $conversionService,
        private readonly AuditLogService $auditLogService
        // private readonly CoraService $coraService,
        // private readonly StripeService $stripeService
     ) {}

    /**
     * Retorna dados para a visão geral das carteiras do usuário.
     */
    public function getWalletOverview(User $user): array
    {
        Log::debug("Getting wallet overview for user", ['user_id' => $user->id]);
        $user->loadMissing('wallets');
        $fiatWallets = $user->wallets()->orderBy('currency')->get();
        $userIban = $this->generateOrRetrieveUserIban($user); // Lógica para IBAN

        // Calcular saldo total usando o Service de conversão
        $totalFiatBalanceInUSD = $this->conversionService->calculateTotalFiatBalance($user, 'USD');

        // Adicionar valor em USD a cada carteira (opcional)
        // foreach ($fiatWallets as $wallet) {
        //     $wallet->balance_in_usd = $this->conversionService->convertAmount($wallet->balance, $wallet->currency, 'USD');
        // }

        return compact('user', 'fiatWallets', 'userIban', 'totalFiatBalanceInUSD');
    }

    /**
     * Retorna informações necessárias para o formulário de depósito.
     */
    public function getDepositInfo(User $user, ?string $selectedCurrency): array
    {
        Log::debug("Getting deposit info for user", ['user_id' => $user->id, 'currency' => $selectedCurrency]);
        // TODO: Definir moedas aceitas para depósito (config)
        $currencies = config('libertom.deposit_currencies', ['BRL', 'USD', 'EUR']);
        $currency = $selectedCurrency && in_array($selectedCurrency, $currencies) ? $selectedCurrency : $user->preferred_currency;
        if(!in_array($currency, $currencies)) $currency = $currencies[0] ?? null;

        $depositInstructions = null;
        // TODO: Buscar instruções do Gateway apropriado (Cora, Stripe, etc.)
        // Exemplo:
        // if ($currency === 'BRL') {
        //     $depositInstructions = $this->coraService->getPixDepositInstructions($user);
        // } elseif (in_array($currency, ['USD', 'EUR'])) {
        //     $depositInstructions = $this->stripeService->getCardDepositSetup($user, $currency);
        // }

        return compact('user', 'currencies', 'selectedCurrency', 'depositInstructions');
    }

    /**
     * Retorna informações necessárias para o formulário de saque.
     */
    public function getWithdrawalInfo(User $user, ?string $selectedCurrency): array
    {
        Log::debug("Getting withdrawal info for user", ['user_id' => $user->id, 'currency' => $selectedCurrency]);
        $user->loadMissing('wallets');
        $sourceWallet = $selectedCurrency ? $user->wallets->where('balance', '>', 0)->firstWhere('currency', $selectedCurrency) : null;

        // TODO: Buscar métodos de saque disponíveis para a moeda/país (config/gateway)
        $withdrawalMethods = []; // Ex: ['pix' => 'PIX (Brasil)', 'ted' => 'TED (Brasil)', 'swift' => 'Transferência SWIFT']

        // TODO: Buscar limites e taxas de saque (pode variar por método)
        $limits = ['min' => 10, 'max' => 5000]; // Exemplo
        $fees = ['pix' => 0, 'ted' => 5.00]; // Exemplo

        return compact('user', 'sourceWallet', 'withdrawalMethods', 'limits', 'fees');
    }

    /**
     * Processa uma solicitação de saque feita pelo usuário.
     * @throws \App\Exceptions\InsufficientBalanceException
     * @throws \App\Exceptions\TransactionException
     * @throws \Exception
     */
    // public function processWithdrawal(User $user, array $validatedData): Transaction
    // {
    //     $walletId = $validatedData['wallet_id'];
    //     $amount = (float) $validatedData['amount'];
    //     $method = $validatedData['method'];
    //     $recipientDetails = $validatedData['recipient_details'];
    //     Log::info("Processing user withdrawal request", ['user_id' => $user->id, 'data' => $validatedData]);
    //
    //     // TODO: Implementar lógica:
    //     // 1. Verificar senha de transação (se aplicável).
    //     // 2. Buscar carteira de origem com lockForUpdate.
    //     // 3. Verificar saldo. Lançar InsufficientBalanceException.
    //     // 4. Calcular taxa de saque. Verificar se saldo cobre (valor + taxa).
    //     // 5. Iniciar DB::transaction.
    //     // 6. Debitar valor + taxa da carteira (usar bcsub).
    //     // 7. Criar registro de Transaction com status 'pending' ou 'processing'.
    //     // 8. Chamar o serviço do gateway apropriado (Cora, Stripe Payouts, SWIFT API) para iniciar o pagamento externo.
    //     // 9. Se chamada ao gateway falhar, dar rollback e lançar exceção.
    //     // 10. Se sucesso, salvar referência do gateway na transação (metadata).
    //     // 11. Logar auditoria.
    //     // 12. DB::commit.
    //     // 13. Retornar a transação criada.
    //     // NOTA: O status final ('completed'/'failed') pode ser atualizado por um webhook do gateway.
    //
    //     return new Transaction(); // Placeholder
    // }

     /**
      * Gera ou recupera o IBAN do usuário.
      * A lógica exata depende se o IBAN é por usuário ou por carteira.
      */
     private function generateOrRetrieveUserIban(User $user): ?string
     {
          // Exemplo: Se for por usuário e armazenado no User model
          if ($user->iban) return $user->iban;

          // Exemplo: Se for por carteira (pegar da carteira principal, ex: USD)
          // $mainWallet = $user->wallets()->where('currency', 'USD')->first();
          // if ($mainWallet?->iban) return $mainWallet->iban;

          // TODO: Chamar um IbanGeneratorService se precisar gerar um novo
          // Log::info("Generating IBAN for user", ['user_id' => $user->id]);
          // $newIban = IbanGenerator::generate('LT'); // Usar código de país apropriado
          // Salvar no User ou Wallet
          // $user->update(['iban' => $newIban]);
          // return $newIban;

          return 'LTXX XXXX XXXX XXXX XXXX'; // Placeholder
     }
}