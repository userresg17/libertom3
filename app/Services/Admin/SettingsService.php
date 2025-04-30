<?php
namespace App\Services\Admin;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt; // Para criptografar secrets
use App\Services\AuditLogService;

class SettingsService
{
    public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Retorna todas as configurações do BD.
     */
    public function getAllSettings(): array
    {
        // TODO: Decriptografar valores sensíveis se necessário antes de retornar
        $settings = Setting::pluck('value', 'key')->all();
        // Exemplo descriptografia (requer que o Model Setting saiba o que é sensível):
        // foreach($settings as $key => $value) {
        //    if (in_array($key, ['gateways.cora.client_secret', ...])) {
        //       try { $settings[$key] = decrypt($value); } catch (\Exception $e) { $settings[$key] = '********'; }
        //    }
        // }
        return $settings;
    }

    /**
     * Atualiza configurações gerais.
     */
    public function updateGeneralSettings(array $validatedData): void
    {
        $adminUser = Auth::guard('admin')->user();
        Log::info('Admin: Updating general settings', ['admin_id' => $adminUser->id]);

        Setting::setValue('app.name', $validatedData['site_name']);
        // Config::set('app.name', $validatedData['site_name']); // Evitar setar config diretamente se ler do BD

        $maintenanceEnabled = $validatedData['maintenance_mode'] ?? false;
        $currentlyDown = app()->isDownForMaintenance();
        // TODO: Implementar lógica de modo manutenção com Artisan::call e log de auditoria
        if ($maintenanceEnabled && !$currentlyDown) { /* ... Artisan::call('down') ... */ }
        if (!$maintenanceEnabled && $currentlyDown) { /* ... Artisan::call('up') ... */ }
        Setting::setValue('maintenance_mode_enabled', $maintenanceEnabled);

        $this->auditLogService->log('general_settings_updated', null, $validatedData, $adminUser);
        Artisan::call('config:clear');
    }

    /**
     * Atualiza o prefixo da URL do Admin.
     */
    public function updateAdminUrl(string $newPrefix): array
    {
        $adminUser = Auth::guard('admin')->user();
        $oldPrefix = Config::get('app.admin_route_prefix', 'administracao');
        Log::info('Admin: Updating admin URL prefix', ['old' => $oldPrefix, 'new' => $newPrefix, 'admin_id' => $adminUser->id]);

        if ($newPrefix === $oldPrefix) return ['status' => 'info', 'message' => 'Nenhuma alteração na URL.', 'requires_logout' => false];

        // TODO: Implementar lógica:
        // 1. Salvar novo prefixo no Setting Model.
        // 2. Limpar cache de configuração (Artisan::call).
        // 3. Logar auditoria.
        // 4. Deslogar admin atual.
        // 5. Retornar dados para redirect.

         Setting::setValue('admin_route_prefix', $newPrefix);
         $this->auditLogService->log('admin_url_updated', null, ['old' => $oldPrefix, 'new' => $newPrefix], $adminUser);
         Artisan::call('config:clear');
         Auth::guard('admin')->logout();

         return [ /* ... dados de redirect ... */
             'status' => 'success', 'message' => 'URL atualizada. Faça login.',
             'requires_logout' => true, 'redirect_to' => '/'. $newPrefix .'/login'
         ];
    }

    /**
     * Atualiza configurações dos Gateways (Cora, Stripe, etc.).
     */
    public function updateGatewaySettings(array $validatedData): void
    {
         $adminUser = Auth::guard('admin')->user();
         Log::info('Admin: Updating gateway settings', ['admin_id' => $adminUser->id]);

         // TODO: Implementar lógica de salvar settings:
         // 1. Iterar sobre $validatedData.
         // 2. Para chaves sensíveis (secret, key), criptografar usando Crypt::encryptString().
         // 3. Salvar cada chave/valor usando Setting::setValue().
         // 4. Logar auditoria (sem logar os valores das chaves!).

         // Exemplo (SEM CRIPTOGRAFIA - IMPLEMENTAR!)
         Setting::setValue('gateways.cora.enabled', $validatedData['cora_enabled'] ?? false);
         Setting::setValue('gateways.cora.client_id', $validatedData['cora_client_id'] ?? '');
         if (!empty($validatedData['cora_client_secret'])) {
             Setting::setValue('gateways.cora.client_secret', $validatedData['cora_client_secret']); // Crypt::encryptString(...)
         }
         // ... fazer o mesmo para Stripe e outros ...

         $this->auditLogService->log('gateway_settings_updated', null, ['keys_updated' => array_keys($validatedData)], $adminUser);
         Artisan::call('config:clear');
    }

     /**
      * Atualiza configurações de Saque.
      */
     public function updateWithdrawalSettings(array $validatedData): void
     {
          $adminUser = Auth::guard('admin')->user();
          Log::info('Admin: Updating withdrawal settings', ['admin_id' => $adminUser->id]);

          // TODO: Salvar configurações usando Setting::setValue()
          Setting::setValue('withdrawals.auto_approve.enabled', $validatedData['withdrawal_auto_approval'] ?? false);
          Setting::setValue('withdrawals.auto_approve.limit_usd', $validatedData['withdrawal_auto_approval_limit_usd'] ?? 0);

          $this->auditLogService->log('withdrawal_settings_updated', null, $validatedData, $adminUser);
          Artisan::call('config:clear');
     }
}