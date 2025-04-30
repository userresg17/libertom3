<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Asset;
use App\Models\Investment;
use App\Models\Transaction;
// TODO: Importar Services (InvestmentService), FormRequests

class InvestmentController extends Controller
{
    /**
     * Exibe o portfólio de investimentos do cliente.
     */
    public function index(): View
    {
        $user = Auth::user();
        $holdings = Investment::where('user_id', $user->id)
                            ->with('asset') // Eager load asset details
                            ->where('quantity', '>', 0) // Apenas holdings com quantidade
                            ->get();

        // TODO: Chamar InvestmentService para calcular totais do portfólio, P/L, variação diária
        $portfolioTotalValue = $holdings->sum('current_value'); // Exemplo usando Accessor
        $portfolioTotalPl = $holdings->sum('total_pl_value'); // Exemplo
        $portfolioTotalPlPercentage = ($portfolioTotalValue - $portfolioTotalPl) > 0 ? ($portfolioTotalPl / ($portfolioTotalValue - $portfolioTotalPl)) * 100 : 0; // Exemplo
        $portfolioDailyChange = 0; // Calcular média ponderada da variação diária dos ativos

        // Buscar histórico de transações de investimento
        $investmentTransactions = Transaction::where('user_id', $user->id)
            ->whereIn('type', ['investment_buy', 'investment_sell'])
            ->with('asset')
            ->latest()
            ->paginate(10, ['*'], 'investment_page'); // Paginação separada

        return view('dashboard.investments', compact(
            'holdings',
            'portfolioTotalValue',
            'portfolioTotalPl',
            'portfolioTotalPlPercentage',
            'portfolioDailyChange',
            'investmentTransactions'
        ));
    }

    /**
     * Exibe página para descobrir/pesquisar ativos.
     */
    public function discover(Request $request): View
    {
        // TODO: Implementar busca/filtragem de Assets (onde is_active = true)
        $query = Asset::where('is_active', true);
        // Aplicar filtros de busca, tipo, etc.
        $assets = $query->paginate(20);

        return view('dashboard.investments.discover', compact('assets')); // Criar esta view
    }

    /**
     * Exibe detalhes de um ativo específico.
     */
    public function assetDetails(Asset $asset): View // Usa Route Model Binding
    {
         if (!$asset->is_active) {
             abort(404); // Ou redirecionar com mensagem
         }
        // TODO: Buscar dados históricos do preço para gráfico (via Service)
        $historicalData = []; // Exemplo

        return view('dashboard.investments.details', compact('asset', 'historicalData')); // Criar esta view
    }

    /**
     * Exibe o formulário/modal para comprar ou vender um ativo.
     */
    public function showTradeForm(Request $request, Asset $asset): View // Ou retornar JSON para modal
    {
        $action = $request->input('action', 'buy'); // 'buy' ou 'sell'
        $user = Auth::user()->loadMissing('wallets');
        $holding = Investment::where('user_id', $user->id)->where('asset_id', $asset->id)->first();
        $availableQuantity = $holding?->quantity ?? 0;

        // TODO: Verificar se o usuário pode negociar (KYC, status, etc.)
        // TODO: Buscar carteira USD do usuário (ou permitir seleção)

        return view('dashboard.investments.trade', compact('asset', 'action', 'user', 'availableQuantity')); // Criar esta view/modal partial
    }

    /**
     * Processa a ordem de compra/venda.
     */
    // public function handleTradeOrder(TradeRequest $request, Asset $asset): RedirectResponse { ... }

}