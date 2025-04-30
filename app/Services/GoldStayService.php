<?php
namespace App\Services;

use App\Models\User;
use App\Models\GoldStayWallet;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;
use App\Services\External\PolygonService; // <-- Placeholder
use App\Services\External\GoldPriceService; // <-- Placeholder
use App\Exceptions\InsufficientBalanceException; // Criar
use App\Exceptions\BlockchainException; // Criar

class GoldStayService
{
     public function __construct(
         private readonly AuditLogService $auditLogService,
         private readonly PolygonService $polygonService,
         private readonly GoldPriceService $goldPriceService
         ) {}

    /**
     * Retorna dados resumidos e endereço do GoldStay para o usuário.
     */
    public function getGoldStayOverview(User $user): array
    {
        Log::debug("Getting GoldStay overview", ['user_id' => $user->id]);
        // TODO: Implementar findOrCreate com lógica de geração de endereço segura
        $wallet = $user->goldStayWallet()->firstOrCreate(
             ['user_id' => $user->id], // Condição para encontrar
             ['polygon_address' => $this->findOrCreateUserAddress($user), 'balance' => 0] // Dados para criar se não encontrar
        );
        $balance = $wallet->balance ?? 0;
        $goldPriceUSD = $this->goldPriceService->getCurrentPricePerGramUSD(); // Implementar este método
        $valueUSD = bcmul((string)$balance, (string)$goldPriceUSD, 2); // Usar bcmath para precisão

        return [
             'user' => $user,
             'goldStayWallet' => $wallet,
             'goldStayBalance' => $balance, // Retorna como string ou float de alta precisão
             'goldStayValueUSD' => $valueUSD, // String formatada com 2 decimais
             'goldPricePerGramUSD' => $goldPriceUSD,
        ];
    }

     /** Encontra ou cria/atribui um endereço Polygon para o usuário */
     private function findOrCreateUserAddress(User $user): string
     {
          // TODO: Implementar geração segura e única de endereços (HD Wallet, etc.)
          $existingWallet = $user->goldStayWallet;
          if ($existingWallet && $existingWallet->polygon_address) {
              return $existingWallet->polygon_address;
          }
          Log::info("Generating new Polygon address for user", ['user_id' => $user->id]);
          $newAddress = '0xGENERATED' . strtoupper(substr(md5($user->email . time() . rand()), 0, 31)); // FAKE - USAR MÉTODO SEGURO
          return $newAddress; // O firstOrCreate no getGoldStayOverview vai salvar se necessário
     }

    /** Retorna o histórico de transações GoldStay do usuário. */
    public function getGoldStayHistory(User $user, array $filters): LengthAwarePaginator
    {
         Log::debug("Getting GoldStay history", ['user_id' => $user->id, 'filters' => $filters]);
          // TODO: Implementar query com filtros (date range)
         $query = Transaction::where('user_id', $user->id)
             ->where(fn($q) => $q->whereIn('type', ['goldstay_buy', 'goldstay_sell', 'goldstay_deposit', 'goldstay_withdraw'])->orWhere('type', 'admin_adjustment_gst'));
         return $query->latest()->paginate(config('libertom.pagination.goldstay_history', 10), ['*'], 'gst_page');
    }

    /** Retorna dados para o formulário de compra de GST. */
    // public function getBuyFormData(User $user): array { /* ... */ }

    /** Processa a compra de GST. */
    // public function buyGoldStay(User $user, array $validatedData): Transaction { /* ... */ }

    /** Retorna dados para o formulário de venda de GST. */
    // public function getSellFormData(User $user): array { /* ... */ }

    /** Processa a venda de GST. */
    // public function sellGoldStay(User $user, array $validatedData): Transaction { /* ... */ }

    /** Retorna dados para depósito de GST. */
    public function getDepositInfo(User $user): array
    {
        Log::debug("Getting GoldStay deposit info", ['user_id' => $user->id]);
        // TODO: Chamar findOrCreateUserAddress para garantir que o endereço existe
        $address = $user->goldStayWallet?->polygon_address ?? $this->findOrCreateUserAddress($user);
        // TODO: Implementar geração de QR Code
        $qrCode = null;
         return compact('user', 'address', 'qrCode');
    }

    /** Retorna dados para o formulário de saque de GST. */
    public function getWithdrawFormData(User $user): array
    {
         Log::debug("Getting GoldStay withdraw form data", ['user_id' => $user->id]);
          // TODO: Chamar findOrCreate para garantir que a wallet existe
         $wallet = $user->goldStayWallet()->firstOrCreate(['polygon_address' => $this->findOrCreateUserAddress($user)]);
         // TODO: Chamar PolygonService para buscar taxa de gás estimada
         $estimatedFee = $this->polygonService->getEstimatedGasFee(); // Exemplo
         return [
            'user' => $user,
            'balance' => $wallet->balance ?? 0,
            'estimatedFee' => $estimatedFee // Ex: ['amount' => '0.01', 'currency' => 'MATIC']
        ];
    }

    /** Processa o saque de GST para um endereço externo. */
    // public function withdrawGoldStay(User $user, array $validatedData): Transaction
    // {
    //      Log::info("Initiating GoldStay withdrawal", ['user_id' => $user->id]);
    //      // TODO: Implementar lógica completa (verificar saldo, taxa, debitar interno, chamar PolygonService->sendToken, criar tx, logar, commit/rollback)
    // }

    // --- Métodos para Webhooks/Listeners ---
    /** Processa um depósito confirmado na blockchain. */
    // public function handleConfirmedDeposit(string $txHash, string $toAddress, string $amount): void { /* ... Encontra usuário, credita saldo interno (bcadd), cria tx ... */ }
    /** Atualiza status de saque após confirmação na blockchain. */
    // public function handleConfirmedWithdrawal(string $txHash, bool $success): void { /* ... Atualiza status da tx 'goldstay_withdraw' para completed/failed ... */ }
}