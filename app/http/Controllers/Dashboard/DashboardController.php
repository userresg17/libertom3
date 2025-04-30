<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
// TODO: Importar Models/Services necessários para buscar dados resumidos

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal do cliente.
     */
    public function index(): View
    {
        $user = Auth::user()->loadMissing(['wallets', 'goldStayWallet', 'investments.asset']); // Eager load básico

        // TODO: Calcular saldos totais, buscar atividade recente, etc. via Services ou queries complexas
        $totalFiatBalanceInUSD = 0; // Exemplo - Calcular via Service
        foreach ($user->wallets as $wallet) {
             // Exemplo muito simplificado - Usar CurrencyConversionService
             $rate = ($wallet->currency === 'USD') ? 1 : ($wallet->currency === 'BRL' ? 0.19 : 1);
             $totalFiatBalanceInUSD += $wallet->balance * $rate;
        }

        $goldStayBalance = $user->goldStayWallet?->balance ?? 0;
        $goldStayValueUSD = $goldStayBalance * 75; // Exemplo - Pegar cotação do ouro via Service

        $totalInvestmentValueUSD = 0; // Exemplo - Calcular via Service
        $portfolioChange24h = 0; // Exemplo - Calcular via Service

        $recentActivity = \App\Models\Transaction::where('user_id', $user->id) // Exemplo
                                ->latest()
                                ->take(5)
                                ->get();

        return view('dashboard.index', compact(
            'user', // Passar o usuário para acesso fácil na view
            'totalFiatBalanceInUSD',
            'goldStayBalance',
            'goldStayValueUSD',
            'totalInvestmentValueUSD',
            'portfolioChange24h',
            'recentActivity'
        ));
    }
}