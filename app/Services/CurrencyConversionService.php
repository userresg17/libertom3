<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http; // Para API de Câmbio
use Illuminate\Support\Facades\Cache; // Para cachear taxas
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Para transações de conversão
use App\Models\Transaction; // Para registrar conversão
use App\Models\Wallet; // Para debitar/creditar

class CurrencyConversionService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct(private readonly AuditLogService $auditLogService)
    {
        // TODO: Ler URL e API Key da API de Câmbio (ex: ExchangeRate-API, Open Exchange Rates) do config/services.php ou .env
        $this->apiKey = config('services.exchange_rate.key');
        $this->baseUrl = config('services.exchange_rate.url', 'https://v6.exchangerate-api.com/v6/'); // Exemplo
    }

    /**
     * Obtém a taxa de câmbio entre duas moedas.
     * Implementa cache.
     */
    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);
        if ($fromCurrency === $toCurrency) return 1.0;

        $cacheKey = "exchange_rate_{$fromCurrency}_{$toCurrency}";
        $cacheDuration = now()->addHours(1); // Cache por 1 hora (ajustar)

        return Cache::remember($cacheKey, $cacheDuration, function () use ($fromCurrency, $toCurrency) {
             Log::debug("Fetching exchange rate from API", ['from' => $fromCurrency, 'to' => $toCurrency]);
            try {
                // TODO: Implementar chamada real à API de Câmbio
                // Exemplo com ExchangeRate-API:
                // $response = Http::get("{$this->baseUrl}{$this->apiKey}/pair/{$fromCurrency}/{$toCurrency}");
                // if ($response->successful() && $response->json('result') === 'success') {
                //     return (float) $response->json('conversion_rate');
                // }

                // Simulação:
                if($fromCurrency == 'USD' && $toCurrency == 'BRL') return 5.15;
                if($fromCurrency == 'BRL' && $toCurrency == 'USD') return 0.194;
                if($fromCurrency == 'USD' && $toCurrency == 'EUR') return 0.92;
                // ... outras taxas simuladas ...

                Log::error("Failed to fetch exchange rate from API", ['from' => $fromCurrency, 'to' => $toCurrency/*, 'response' => $response->body()*/]);
                return null;
            } catch (\Exception $e) {
                Log::error("Exception fetching exchange rate", ['from' => $fromCurrency, 'to' => $toCurrency, 'error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Calcula o saldo total fiat de um usuário em uma moeda base.
     */
    public function calculateTotalFiatBalance(User $user, string $baseCurrency = 'USD'): float
    {
        $total = 0.0;
        foreach ($user->wallets as $wallet) {
            if ($wallet->currency === $baseCurrency) {
                $total += $wallet->balance;
            } else {
                $rate = $this->getExchangeRate($wallet->currency, $baseCurrency);
                if ($rate) {
                    $total += $wallet->balance * $rate;
                } else {
                    Log::warning("Could not get rate to calculate total balance", ['wallet_currency' => $wallet->currency, 'base' => $baseCurrency]);
                }
            }
        }
        // Arredondar para precisão da moeda base?
        return round($total, 2);
    }

    /**
     * Obtém dados para o formulário de conversão.
     */
    public function getConversionFormData(User $user, ?string $from = null, ?string $to = null): array
    {
         $user->loadMissing('wallets');
         $wallets = $user->wallets->where('balance', '>', 0);
         $fromCurrency = $from ?? $wallets->first()?->currency;
         $toCurrency = $to;
         // TODO: Buscar taxa inicial se as moedas estiverem definidas
         $initialRate = ($fromCurrency && $toCurrency) ? $this->getExchangeRate($fromCurrency, $toCurrency) : null;

         return [
             'user' => $user,
             'wallets' => $wallets,
             'fromCurrency' => $fromCurrency,
             'toCurrency' => $toCurrency,
             'initialRate' => $initialRate,
             // Passar lista de moedas disponíveis para conversão
             'availableCurrencies' => $user->wallets->pluck('currency')->unique()->sort()->values()->all(),
         ];
    }

     /**
      * Executa a conversão de moeda entre carteiras do usuário.
      * @throws \Exception Em caso de erro (saldo insuficiente, taxa inválida, etc.).
      */
     // public function convertCurrency(User $user, array $validatedData): array // Retorna as duas transações criadas
     // {
     //     $fromWallet = Wallet::where('id', $validatedData['from_wallet_id'])->where('user_id', $user->id)->lockForUpdate()->firstOrFail();
     //     $toCurrency = $validatedData['to_currency'];
     //     $amountFrom = (float) $validatedData['amount_from'];
     //     $fromCurrency = $fromWallet->currency;
     //
     //     if ($fromWallet->balance < $amountFrom) {
     //         throw new \App\Exceptions\InsufficientBalanceException("Saldo insuficiente na carteira {$fromCurrency}.");
     //     }
     //
     //     $rate = $this->getExchangeRate($fromCurrency, $toCurrency);
     //     if (!$rate) {
     //         throw new \App\Exceptions\ExchangeRateException("Não foi possível obter a taxa de câmbio de {$fromCurrency} para {$toCurrency}.");
     //     }
     //
     //     $amountTo = $amountFrom * $rate;
     //     // TODO: Aplicar taxa de conversão (spread) se houver
     //     // $fee = $amountFrom * config('libertom.conversion_fee_percent', 0.005); // 0.5%
     //     // $amountTo = ($amountFrom - $fee) * $rate;
     //
     //     $toWallet = $user->wallets()->firstOrCreate(
     //         ['currency' => $toCurrency],
     //         ['balance' => 0, 'decimal_places' => ($toCurrency === 'JPY' ? 0 : 2)]
     //     );
     //     $toWallet = Wallet::where('id', $toWallet->id)->lockForUpdate()->first(); // Lock na carteira de destino também
     //
     //     DB::beginTransaction();
     //     try {
     //         // Debitar da origem
     //         $oldBalanceFrom = $fromWallet->balance;
     //         $fromWallet->balance = bcsub((string)$oldBalanceFrom, (string)$amountFrom, 8);
     //         $fromWallet->save();
     //
     //         // Creditar no destino
     //         $oldBalanceTo = $toWallet->balance;
     //         $toWallet->balance = bcadd((string)$oldBalanceTo, (string)$amountTo, $toWallet->decimal_places + 4); // Usar precisão alta no cálculo
     //          // Arredondar para a precisão final da moeda de destino
     //          $toWallet->balance = number_format($toWallet->balance, $toWallet->decimal_places, '.', '');
     //         $toWallet->save();
     //
     //         // Criar transações (uma de débito, uma de crédito)
     //         $txFrom = Transaction::create([ /* ... tipo conversion_from ... */ ]);
     //         $txTo = Transaction::create([ /* ... tipo conversion_to, related_transaction_id = $txFrom->id ... */ ]);
     //
     //         $this->auditLogService->log('currency_converted', $user, $validatedData);
     //         DB::commit();
     //         return ['from' => $txFrom, 'to' => $txTo];
     //
     //     } catch (\Exception $e) {
     //         DB::rollBack();
     //         Log::error("Currency conversion failed", ['user_id' => $user->id, 'error' => $e->getMessage()]);
     //         throw $e;
     //     }
     // }


     /**
      * Obtém cotação para transferência internacional (usado por SendMoneyController).
      */
     // public function getTransferQuote(array $validatedData): ?array
     // {
     //     $source = $validatedData['source_currency'];
     //     $dest = $validatedData['destination_currency'];
     //     $amount = $validatedData['amount_sent'];
     //
     //     $rate = $this->getExchangeRate($source, $dest);
     //     if (!$rate) return null;
     //
     //     // TODO: Calcular taxa de transferência (pode variar por país/moeda/gateway)
     //     $fee = 5.00; // Exemplo de taxa fixa em USD (converter para $source)
     //     $feeSourceCurrency = $this->convertAmount($fee, 'USD', $source); // Precisa de um método auxiliar
     //     if (is_null($feeSourceCurrency)) $feeSourceCurrency = $fee * 5; // Fallback ruim
     //
     //     // TODO: Adicionar spread à taxa de câmbio
     //     $rateWithSpread = $rate * (1 - config('libertom.transfer_spread_percent', 0.01)); // 1% spread exemplo
     //
     //     $totalDebit = $amount + $feeSourceCurrency;
     //     $amountReceived = $amount * $rateWithSpread;
     //
     //     // TODO: Obter tempo estimado (pode vir de API de parceiro)
     //     $estimatedTime = '1-3 dias úteis';
     //     $quoteId = 'QID_' . time() . Str::random(8);
     //     $validSeconds = 300; // 5 minutos
     //
     //     // TODO: Salvar cotação em cache ou BD temporário com validade
     //     Cache::put('transfer_quote_'.$quoteId, compact('source', 'dest', 'amount', 'rateWithSpread', 'feeSourceCurrency', 'amountReceived'), now()->addSeconds($validSeconds));
     //
     //     return [
     //         'rate' => $rateWithSpread,
     //         'fee' => $feeSourceCurrency,
     //         'total_debit' => $totalDebit,
     //         'amount_received' => $amountReceived,
     //         'estimated_time' => $estimatedTime,
     //         'quote_valid_seconds' => $validSeconds,
     //         'quote_id' => $quoteId,
     //     ];
     // }

      /** Método auxiliar para converter um valor entre moedas (usando getExchangeRate) */
      // public function convertAmount(float $amount, string $from, string $to): ?float { ... }

}