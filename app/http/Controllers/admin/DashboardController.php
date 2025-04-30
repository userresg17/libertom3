<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
// TODO: Importar Models ou Services necessários para buscar KPIs

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard principal do admin com KPIs.
     */
    public function index(): View
    {
        // TODO: Buscar KPIs do banco de dados ou via Service
        // Exemplo: Número de usuários, Saldo total, Volume de Investimentos, Transações recentes, etc.
        $kpis = [
            'total_users' => \App\Models\User::count(),
            'total_balance_usd' => 150000.75, // Exemplo - Buscar e converter saldos
            'total_investments_usd' => 75000.50, // Exemplo - Buscar e calcular valor
            'transactions_24h' => 120, // Exemplo
        ];

        $recentTransactions = \App\Models\Transaction::with('user') // Exemplo
                                ->latest()
                                ->take(10)
                                ->get();

        // TODO: Buscar outros dados relevantes para o dashboard admin

        return view('admin.index', compact('kpis', 'recentTransactions'));
    }
}