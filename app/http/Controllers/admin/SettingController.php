<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request; // Usar FormRequests depois
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan; // Para modo manutenção
use Illuminate\Support\Facades\Config; // Para ler/escrever config
use App\Rules\ValidAdminRoutePrefix; // <-- Criar esta regra customizada
// TODO: Importar AuditLog Service

class SettingController extends Controller
{
    /**
     * Exibir página de configurações.
     */
    public function index(): View
    {
        // Carregar configurações existentes (pode usar o helper do Model ou buscar todas)
        $settings = Setting::pluck('value', 'key')->all(); // Pega todas como array key=>value
        return view('admin.settings.index', compact('settings')); // Ajustar caminho: admin.settings
    }

    /**
     * Atualizar configurações gerais.
     */
    public function updateGeneral(Request $request): RedirectResponse // <-- Usar FormRequest: UpdateGeneralSettingsRequest
    {
        // TODO: Validação
        $request->validate([
            'site_name' => 'required|string|max:255',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        // TODO: Logar ação
        // AuditLogService::log('general_settings_updated', null, $request->all(), auth('admin')->user());


        Setting::setValue('app.name', $request->input('site_name'));
        // Atualizar config em tempo real também
        Config::set('app.name', $request->input('site_name'));
        // Atualizar .env? É mais complexo e arriscado programaticamente.
        // Geralmente settings do BD ou config/ são suficientes.

        // Modo Manutenção
        if ($request->boolean('maintenance_mode')) {
            Artisan::call('down', ['--secret' => 'libertom-secret']); // Usar um secret é boa prática
            Setting::setValue('maintenance_mode_enabled', true);
        } else {
             // Verificar se estava em modo manutenção antes de tentar 'up'
             if (app()->isDownForMaintenance()) {
                Artisan::call('up');
             }
             Setting::setValue('maintenance_mode_enabled', false);
        }

        return redirect()->route('admin.settings.index')->with('success', 'Configurações gerais atualizadas.');
    }

    /**
     * Atualizar URL do Admin.
     */
    public function updateAdminUrl(Request $request): RedirectResponse // <-- Usar FormRequest: UpdateAdminUrlRequest
    {
        // TODO: Validação (incluindo a regra customizada)
        $request->validate([
            'admin_url_path' => ['required', 'string', 'alpha_dash', 'max:100', new ValidAdminRoutePrefix],
        ]);

        $newPrefix = $request->input('admin_url_path');
        $oldPrefix = config('app.admin_route_prefix', 'administracao');

        if ($newPrefix !== $oldPrefix) {
            // **ATENÇÃO:** Alterar prefixo dinamicamente é complexo e pode requerer
            // escrita no .env ou em um arquivo de config gerenciado.
            // A forma mais simples é armazenar no BD e usar um Middleware/Provider para ler
            // o prefixo do BD e aplicá-lo dinamicamente às rotas admin.php.

            // Exemplo: Armazenar no BD
            Setting::setValue('admin_route_prefix', $newPrefix);

             // TODO: Logar ação
             // AuditLogService::log('admin_url_updated', null, ['old' => $oldPrefix, 'new' => $newPrefix], auth('admin')->user());

            // Forçar logout do admin para que ele acesse pela nova URL
            auth('admin')->logout(); // Assume guard 'admin'

            return redirect('/' . $newPrefix . '/login') // Redireciona para a nova URL de login
                   ->with('success', 'URL do Admin atualizada. Faça login novamente.');
        }

        return redirect()->route('admin.settings.index')->with('info', 'Nenhuma alteração na URL do Admin.');
    }

    /**
     * Atualizar configurações dos Gateways de Pagamento.
     */
    public function updateGateways(Request $request): RedirectResponse // <-- Usar FormRequest: UpdateGatewaySettingsRequest
    {
         // TODO: Validação (habilitado, chaves, segredos)
         // Exemplo para Cora:
         $validated = $request->validate([
             'cora_enabled' => 'nullable|boolean',
             'cora_client_id' => 'nullable|string|required_if:cora_enabled,1',
             'cora_client_secret' => 'nullable|string', // Não required para não obrigar a mudar
             // Stripe...
             'stripe_enabled' => 'nullable|boolean',
             'stripe_key' => 'nullable|string|required_if:stripe_enabled,1',
             'stripe_secret' => 'nullable|string',
             'stripe_webhook_secret' => 'nullable|string',
         ]);

         // TODO: Logar ação
         // AuditLogService::log('gateway_settings_updated', null, [], auth('admin')->user());


         // Salvar no BD usando o Model Setting
         Setting::setValue('gateways.cora.enabled', $request->boolean('cora_enabled'));
         Setting::setValue('gateways.cora.client_id', $validated['cora_client_id'] ?? '');
         if (!empty($validated['cora_client_secret'])) {
             Setting::setValue('gateways.cora.client_secret', $validated['cora_client_secret']); // Idealmente criptografar antes de salvar
         }

         Setting::setValue('gateways.stripe.enabled', $request->boolean('stripe_enabled'));
         Setting::setValue('gateways.stripe.key', $validated['stripe_key'] ?? '');
         if (!empty($validated['stripe_secret'])) {
             Setting::setValue('gateways.stripe.secret', $validated['stripe_secret']); // Idealmente criptografar
         }
         if (!empty($validated['stripe_webhook_secret'])) {
             Setting::setValue('gateways.stripe.webhook_secret', $validated['stripe_webhook_secret']); // Idealmente criptografar
         }

         // **IMPORTANTE:** O ideal seria armazenar chaves sensíveis criptografadas no BD
         // ou diretamente no .env (mais seguro, mas requer acesso ao servidor para mudar).
         // Se usar .env, este painel não poderia alterá-las. Se usar BD criptografado,
         // precisará de lógica para descriptografar ao usar e criptografar ao salvar.

         // Limpar cache de config se estiver usando config() para ler do BD
         Artisan::call('config:clear'); // Ou Cache::forget(...)

        return redirect()->route('admin.settings.index', ['#gateways'])->with('success', 'Configurações dos Gateways atualizadas.'); # Leva para a tab correta
    }

    /**
     * Atualizar configurações de Saque.
     */
    public function updateWithdrawals(Request $request): RedirectResponse // <-- Usar FormRequest: UpdateWithdrawalSettingsRequest
    {
        // TODO: Validação
         $validated = $request->validate([
            'withdrawal_auto_approval' => 'nullable|boolean',
            'withdrawal_auto_approval_limit_usd' => 'nullable|required_if:withdrawal_auto_approval,1|numeric|min:0',
         ]);

         // TODO: Logar ação
         // AuditLogService::log('withdrawal_settings_updated', null, $validated, auth('admin')->user());

          Setting::setValue('withdrawals.auto_approve.enabled', $request->boolean('withdrawal_auto_approval'));
          Setting::setValue('withdrawals.auto_approve.limit_usd', $validated['withdrawal_auto_approval_limit_usd'] ?? 0);

          Artisan::call('config:clear');

        return redirect()->route('admin.settings.index', ['#withdrawals'])->with('success', 'Configurações de Saque atualizadas.');
    }
}