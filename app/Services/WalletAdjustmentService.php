<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Models\Wallet; // Importar
use App\Models\GoldStayWallet; // Importar
use App\Models\Transaction; // Importar
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogService;
use App\Exceptions\WalletException; // Criar

class WalletAdjustmentService
{
     public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Ajusta saldo Fiat ou GoldStay de um usuário (Ação do Admin).
     * @throws \Exception Em caso de erro.
     */
    public function adjustBalance(User $user, array $validatedData): void
    {
         $adminUser = auth('admin')->user();
         Log::info('Admin: Adjusting balance', ['user_id' => $user->id, 'admin_id' => $adminUser->id, 'data' => $validatedData]);

        DB::beginTransaction();
        try {
            // Ajuste Fiat
            if (!empty($validatedData['fiat_currency']) && isset($validatedData['fiat_amount'])) {
                $this->adjustFiat($user, $validatedData, $adminUser);
            }

             // Ajuste GoldStay
             if (isset($validatedData['gst_amount'])) {
                 $this->adjustGoldStay($user, $validatedData, $adminUser);
             }

            DB::commit();
            Log::info('Admin: Balance adjustment successful', ['user_id' => $user->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Admin: Balance adjustment failed", ['user_id' => $user->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /** Lógica para ajustar carteira Fiat */
    private function adjustFiat(User $user, array $data, $adminUser): void
    {
         $amount = (string) $data['fiat_amount'];
         $currency = $data['fiat_currency'];
         $reason = $data['fiat_reason'] ?? 'Ajuste administrativo';
         Log::debug('Adjusting Fiat', compact('currency', 'amount', 'reason'));

        // TODO: Implementar lógica:
        // 1. Encontrar ou criar a Wallet com lockForUpdate().
        // 2. Calcular novo saldo com bcadd().
        // 3. Salvar Wallet.
        // 4. Criar Transaction 'admin_adjustment'.
        // 5. Logar auditoria.

        // Exemplo simplificado:
         $wallet = $user->wallets()->firstOrCreate(['currency' => $currency], ['balance' => 0, 'decimal_places' => 2]);
         $wallet->increment('balance', (float)$amount); // CUIDADO: Não use increment/decrement para dinheiro real, use bcmath e lock!
         // ... criar transaction e log ...
         $this->auditLogService->log('fiat_balance_adjusted', $user, $data, $adminUser);

    }

    /** Lógica para ajustar carteira GoldStay */
    private function adjustGoldStay(User $user, array $data, $adminUser): void
    {
        $gstAmount = (string) $data['gst_amount'];
        $reason = $data['gst_reason'] ?? 'Ajuste administrativo';
        Log::debug('Adjusting GoldStay', compact('gstAmount', 'reason'));

        // TODO: Implementar lógica:
        // 1. Encontrar ou criar a GoldStayWallet com lockForUpdate().
        // 2. Calcular novo saldo com bcadd() (precisão 18).
        // 3. Salvar GoldStayWallet.
        // 4. Criar Transaction 'admin_adjustment_gst'.
        // 5. Logar auditoria.

        // Exemplo simplificado:
        $gstWallet = $user->goldStayWallet()->firstOrCreate(['polygon_address' => 'temp_'. $user->id]); // Endereço temporário?
        $gstWallet->balance = bcadd((string)$gstWallet->balance, $gstAmount, 18);
        $gstWallet->save();
         // ... criar transaction e log ...
         $this->auditLogService->log('gst_balance_adjusted', $user, $data, $adminUser);
    }
}