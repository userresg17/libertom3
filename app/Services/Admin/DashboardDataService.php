<?php
namespace App\Services\Admin; // <- Corrigido namespace para Admin

use App\Models\User;
use App\Models\Transaction;
use App\Models\Wallet; // Para calcular saldos
use App\Models\Investment; // Para calcular investimentos
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CurrencyConversionService; // <- Service comum

class DashboardDataService // Renomeado de AdminDashboardService para consistência
{
    public function __construct(private readonly CurrencyConversionService $conversionService) {}

    /**
     * Busca os principais KPIs para o dashboard do admin.
     */
    public function getKpis(): array
    {
        Log::debug("Admin: Fetching KPIs");
        // TODO: Implementar queries otimizadas ou buscar de tabela de resumo se performance for crítica
        $totalUsers = User::count();

        // Calcular saldo total convertido (exemplo - usar CurrencyConversionService real)
        $totalBalanceUSD = Wallet::all()->reduce(function ($carry, $wallet) {
            $rate = $this->conversionService->getBaseExchangeRate($wallet->currency, 'USD') ?? 0;
            return $carry + ($wallet->balance * $rate);
        }, 0);

        // Calcular valor total de investimentos (exemplo)
        $totalInvestmentsUSD = Investment::join('assets', 'investments.asset_id', '=', 'assets.id')
                                         ->sum(DB::raw('investments.quantity * assets.current_price'));

        $transactions24h = Transaction::where('created_at', '>=', now()->subDay())->count();

        return [
            'total_users' => $totalUsers,
            'total_balance_usd' => round($totalBalanceUSD, 2),
            'total_investments_usd' => round($totalInvestmentsUSD, 2),
            'transactions_24h' => $transactions24h,
            // Adicionar outros KPIs relevantes (ex: Saques pendentes, Novos usuários 24h)
        ];
    }

    /**
     * Busca as transações recentes para o dashboard do admin.
     */
    public function getRecentTransactions(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        Log::debug("Admin: Fetching recent transactions", ['limit' => $limit]);
        // TODO: Considerar performance em tabelas grandes, talvez otimizar query
        return Transaction::with('user:id,name')->latest()->take($limit)->get();
    }
}