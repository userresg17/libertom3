<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
// TODO: Importar TransferService, CurrencyConversionService, Models (User, Wallet, Transaction), FormRequests (SendMoneyRequest)

class SendMoneyController extends Controller
{
    // private $transferService;
    // private $currencyConversionService;

    // public function __construct(TransferService $ts, CurrencyConversionService $ccs)
    // {
    //     $this->transferService = $ts;
    //     $this->currencyConversionService = $ccs;
    // }

    /**
     * Exibe o formulário de envio de dinheiro.
     */
    public function index(): View
    {
        $user = Auth::user()->loadMissing('wallets');
        $userWallets = $user->wallets()->where('balance', '>', 0)->get();

        // TODO: Buscar países e moedas suportados para destino (de config ou Service)
        $supportedCountries = ['US' => 'Estados Unidos', 'GB' => 'Reino Unido', 'DE' => 'Alemanha', 'BR' => 'Brasil', 'AR' => 'Argentina', /* ... */ ];
        $supportedCurrencies = ['USD', 'GBP', 'EUR', 'BRL', 'ARS', /* ... */];

        return view('dashboard.sendmoney', compact('user', 'userWallets', 'supportedCountries', 'supportedCurrencies'));
    }

    /**
     * Obtém uma cotação para a transferência (Endpoint para AJAX/Fetch do Alpine.js).
     * Idealmente, esta rota estaria em routes/api.php
     */
    public function getQuote(Request $request): JsonResponse
    {
        // TODO: Validação básica dos inputs (source_currency, dest_currency, amount)
        $request->validate([
            'source_currency' => 'required|string|size:3',
            'destination_currency' => 'required|string|size:3',
            'amount_sent' => 'required|numeric|gt:0',
        ]);

        // TODO: Chamar CurrencyConversionService->getQuote(...)
        // Este serviço calcularia a taxa, as fees e retornaria a estrutura esperada pelo Alpine
        $quoteData = [ // Exemplo FAKE
            'rate' => 0.92,
            'fee' => 5.00,
            'total_debit' => (float) $request->input('amount_sent') + 5.00,
            'amount_received' => $request->input('amount_sent') * 0.92,
            'estimated_time' => '1-3 dias úteis',
            'quote_valid_seconds' => 300,
            'quote_id' => 'QUOTE_' . time() . rand(100, 999)
        ];

        if ($quoteData) {
            return response()->json($quoteData);
        } else {
            return response()->json(['error' => 'Não foi possível obter a cotação.'], 400);
        }
    }


    /**
     * Processa a submissão da transferência.
     */
    // public function submitTransfer(SendMoneyRequest $request): RedirectResponse // Ou JsonResponse
    // {
    //     // 1. Validar (Request - inclui quote_id, campos dinâmicos do destinatário, etc.)
    //     // 2. Verificar cotação (TransferService->validateQuote($request->quote_id, $request->amount_sent, ...))
    //     // 3. Verificar senha de transação (Auth::user()->verifyTransactionPassword(...))
    //     // 4. Verificar saldo da carteira de origem
    //     // 5. Chamar TransferService->initiateTransfer(...)
    //     //    - Debita da carteira de origem
    //     //    - Cria transação 'transfer_out' com status 'processing'
    //     //    - Inicia a transferência via API do gateway (Stripe Connect, SWIFT API, etc.)
    //     // 6. Logar ação
    //     // 7. Redirecionar para página de sucesso ou retornar JSON
    // }

}