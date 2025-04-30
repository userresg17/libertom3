<?php
namespace App\Services; // Ou App\Services\Dashboard

use App\Models\User;
use App\Models\Asset;
use App\Models\Investment;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;
use App\Exceptions\InsufficientBalanceException; // Criar
use App\Exceptions\InsufficientHoldingException; // Criar
use App\Exceptions\TradingException; // Criar
// use App\Services\External\MarketDataService; // Placeholder
// use App\Services\External\BrokerageService; // Placeholder

class InvestmentService
{
     public function __construct(
         private readonly AuditLogService $auditLogService
         // private readonly MarketDataService $marketDataService,
         // private readonly BrokerageService $brokerageService
     ) {}

    /**
     * Retorna dados resumidos do portfólio para o dashboard do cliente.
     */
    public function getPortfolioOverview(User $user): array
    {
         Log::debug("Getting portfolio overview", ['user_id' => $user->id]);
         // TODO: Implementar cálculo real usando relacionamentos e accessors dos models
         // $holdings = $user->investments()->with('asset')->where('quantity', '>', 0)->get();
         // $totalValue = $holdings->sum('current_value');
         // $totalPl = $holdings->sum('total_pl_value');
         // $totalCost = $holdings->sum('total_cost');
         // $totalPlPercent = ($totalCost > 0) ? ($totalPl / $totalCost) * 100 : 0;
         // $dailyChange = ... // Média ponderada da variação dos ativos
         return [
             'holdings' => $user->investments()->with('asset')->where('quantity', '>', 0)->get(), // Exemplo
             'portfolioTotalValue' => 0.00, // Placeholder
             'portfolioTotalPl' => 0.00, // Placeholder
             'portfolioTotalPlPercentage' => 0.00, // Placeholder
             'portfolioDailyChange' => 0.00, // Placeholder %
         ];
    }

    /**
     * Retorna o histórico de transações de investimento do usuário.
     */
    public function getInvestmentHistory(User $user, array $filters): LengthAwarePaginator
    {
         Log::debug("Getting investment history", ['user_id' => $user->id, 'filters' => $filters]);
         // TODO: Implementar query com filtros (asset, date range)
         $query = Transaction::where('user_id', $user->id)
             ->whereIn('type', ['investment_buy', 'investment_sell'])
             ->with('asset');
         return $query->latest()->paginate(config('libertom.pagination.investment_history', 10), ['*'], 'investment_page');
    }

    /**
     * Lista ativos disponíveis para investimento pelo cliente.
     */
    public function listAvailableAssets(array $filters): LengthAwarePaginator
    {
        Log::debug("Listing available assets for investment", $filters);
         // TODO: Implementar query com filtros (search, type)
        $query = Asset::where('is_active', true);
        return $query->paginate(config('libertom.pagination.discover_assets', 20));
    }

    /**
     * Busca detalhes de um ativo, incluindo dados históricos.
     */
    public function getAssetDetails(Asset $asset): array
    {
         Log::debug("Getting asset details", ['asset_id' => $asset->id]);
         // TODO: Chamar MarketDataService para buscar histórico de preços
         // $historicalData = $this->marketDataService->getHistory($asset->symbol, '1Y');
         return [
             'asset' => $asset,
             'historicalData' => [], // Placeholder para gráfico
         ];
    }

    /**
     * Prepara dados para o formulário/modal de negociação do cliente.
     */
    public function getTradeFormData(User $user, Asset $asset, string $action): array
    {
         Log::debug("Getting trade form data", ['user_id' => $user->id, 'asset_id' => $asset->id, 'action' => $action]);
         // TODO: Implementar busca de holding, carteira de pagamento (USD?)
         $user->loadMissing('wallets');
         $holding = $user->investments()->where('asset_id', $asset->id)->first();
         $paymentWallet = $user->wallets()->where('currency', 'USD')->first(); // Assume USD

         return [
            'user' => $user,
            'asset' => $asset,
            'action' => $action,
            'availableQuantity' => $holding?->quantity ?? 0,
            'paymentWallet' => $paymentWallet,
         ];
    }

    /**
     * Executa uma ordem de compra ou venda para o cliente.
     * @throws \App\Exceptions\InsufficientBalanceException | \App\Exceptions\InsufficientHoldingException | \App\Exceptions\TradingException | \Exception
     */
    // public function placeOrder(User $user, Asset $asset, array $validatedData): Transaction
    // {
    //      Log::info("Placing trade order", ['user_id' => $user->id, 'asset_id' => $asset->id, 'data' => $validatedData]);
    //      // TODO: Implementar lógica completa descrita anteriormente (verificar saldo/posse, calcular valor/taxa, debitar/creditar wallet, atualizar/criar Investment, criar Transaction, logar auditoria, commit/rollback)
    //      return new Transaction(); // Placeholder
    // }
}