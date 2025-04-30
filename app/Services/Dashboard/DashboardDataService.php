<?php
namespace App\Services\Dashboard; // <- Corrigido namespace para Dashboard

use App\Models\User;
use App\Models\Transaction;
use App\Services\CurrencyConversionService; // <-- Service comum
use App\Services\InvestmentService; // <-- Service de investimento do cliente
use App\Services\GoldStayService; // <-- Service do GoldStay
use Illuminate\Support\Facades\Log;

class DashboardDataService
{
    public function __construct(
        private readonly CurrencyConversionService $conversionService,
        private readonly InvestmentService $investmentService,
        private readonly GoldStayService $goldStayService
    ) {}

    /**
     * Busca dados resumidos para o dashboard do cliente.
     */
    public function getDashboardSummary(User $user): array
    {
        Log::debug("Dashboard: Getting summary data", ['user_id' => $user->id]);
        $user->loadMissing(['wallets', 'goldStayWallet']); // Garante que wallets estão carregadas

        $totalFiatBalanceInUSD = $this->conversionService->calculateTotalFiatBalance($user, 'USD');
        $goldStayData = $this->goldStayService->getGoldStayOverview($user); // Pega saldo e valor
        $investmentData = $this->investmentService->getPortfolioSummary($user); // Pega valor e variação
        $recentActivity = Transaction::where('user_id', $user->id)
                                // TODO: Filtrar por tipos mais relevantes para o cliente?
                                ->orderByDesc('created_at')
                                ->take(config('libertom.pagination.dashboard_activity', 5))
                                ->get();

        return [
            'user' => $user,
            'totalFiatBalanceInUSD' => $totalFiatBalanceInUSD,
            'goldStayBalance' => $goldStayData['goldStayBalance'] ?? 0,
            'goldStayValueUSD' => $goldStayData['goldStayValueUSD'] ?? 0,
            'totalInvestmentValueUSD' => $investmentData['portfolioTotalValue'] ?? 0,
            'portfolioChange24h' => $investmentData['portfolioDailyChange'] ?? 0, // Passa a variação %
            'recentActivity' => $recentActivity,
        ];
    }
}