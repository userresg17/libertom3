<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// TODO: Importar Models (Wallet, Transaction), Services (CurrencyConversion, Deposit, Withdrawal), FormRequests

class WalletController extends Controller
{
    /**
     * Exibe as carteiras fiat do usuário.
     */
    public function index(): View
    {
        $user = Auth::user();
        $fiatWallets = $user->wallets()->orderBy('currency')->get(); // Ordenar por moeda
        $userIban = $user->iban; // Ou buscar de Wallet se for por carteira

        // TODO: Calcular saldo total fiat via Service/Helper
        $totalFiatBalanceInUSD = 0; // Exemplo

        // Opcional: Pré-calcular valores em USD para cada carteira (pode ser pesado)
        // foreach ($fiatWallets as $wallet) {
        //     $wallet->balance_in_usd = CurrencyConversionService::convert($wallet->balance, $wallet->currency, 'USD');
        // }

        return view('dashboard.wallets', compact('fiatWallets', 'userIban', 'totalFiatBalanceInUSD', 'user'));
    }

    /**
     * Exibe a interface/página para depósito.
     * Pode variar muito dependendo do método (PIX, TED, Stripe).
     */
    public function showDepositForm(Request $request): View
    {
        $user = Auth::user();
        $currencies = ['BRL', 'USD', 'EUR']; // Moedas que podem ser depositadas
        $selectedCurrency = $request->input('currency', $user->preferred_currency);

        // TODO: Buscar instruções ou dados necessários para o depósito na moeda selecionada
        // Ex: Dados PIX (Cora), Dados bancários, Link Stripe

        return view('dashboard.wallets.deposit', compact('user', 'currencies', 'selectedCurrency')); // Criar esta view
    }

    /**
     * Processa a solicitação de depósito (pode ser um webhook ou confirmação).
     */
    // public function handleDeposit(Request $request): RedirectResponse { ... }

    /**
     * Exibe o formulário de saque.
     */
    public function showWithdrawForm(Request $request): View
    {
         $user = Auth::user()->loadMissing('wallets');
         $selectedCurrency = $request->input('currency');
         $sourceWallet = $selectedCurrency ? $user->wallets->firstWhere('currency', $selectedCurrency) : null;

         // TODO: Buscar métodos de saque disponíveis para a moeda/país
         // TODO: Buscar limites de saque

         return view('dashboard.wallets.withdraw', compact('user', 'sourceWallet')); // Criar esta view
    }

     /**
      * Processa a solicitação de saque.
      */
     // public function handleWithdrawal(WithdrawalRequest $request): RedirectResponse { ... }


    /**
     * Exibe o formulário de conversão de moeda.
     */
    public function showConvertForm(Request $request): View
    {
        $user = Auth::user()->loadMissing('wallets');
        $wallets = $user->wallets->where('balance', '>', 0); // Apenas carteiras com saldo
        $fromCurrency = $request->input('from', $wallets->first()?->currency);
        $toCurrency = $request->input('to');

        // TODO: Obter taxa de conversão inicial via CurrencyConversionService

        return view('dashboard.wallets.convert', compact('user', 'wallets', 'fromCurrency', 'toCurrency')); // Criar esta view
    }

    /**
     * Processa a conversão de moeda.
     */
    // public function handleConversion(ConversionRequest $request): RedirectResponse { ... }

}