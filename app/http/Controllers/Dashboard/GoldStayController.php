<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Transaction;
// TODO: Importar GoldStayService, FormRequests (Buy, Sell, Withdraw)

class GoldStayController extends Controller
{
    /**
     * Exibe a página do GoldStay.
     */
    public function index(): View
    {
        $user = Auth::user()->loadMissing('goldStayWallet');
        $goldStayWallet = $user->goldStayWallet;
        $goldStayBalance = $goldStayWallet?->balance ?? 0;

        // TODO: Obter valor atual do Ouro via Service para calcular valor USD
        $goldPricePerGramUSD = 75.00; // Exemplo - Buscar via Service
        $goldStayValueUSD = $goldStayBalance * $goldPricePerGramUSD;

        // Buscar histórico de transações GoldStay
        $goldstayTransactions = Transaction::where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereIn('type', ['goldstay_buy', 'goldstay_sell', 'goldstay_deposit', 'goldstay_withdraw'])
                      ->orWhere(function($q) { // Inclui ajustes admin de GST
                            $q->where('type', 'admin_adjustment_gst')
                              ->where('currency', 'GST'); // Filtrar por 'moeda' GST
                      });
            })
            ->latest()
            ->paginate(10, ['*'], 'gst_page'); // Paginação separada

        return view('dashboard.goldstay', compact(
            'user',
            'goldStayWallet',
            'goldStayBalance',
            'goldStayValueUSD',
            'goldstayTransactions'
        ));
    }

    /**
     * Exibe formulário para comprar GoldStay.
     */
    // public function showBuyForm(): View { ... }

    /**
     * Processa a compra de GoldStay.
     */
    // public function handleBuy(BuyGoldStayRequest $request): RedirectResponse { ... }

    /**
     * Exibe formulário para vender GoldStay.
     */
    // public function showSellForm(): View { ... }

    /**
     * Processa a venda de GoldStay.
     */
    // public function handleSell(SellGoldStayRequest $request): RedirectResponse { ... }

     /**
      * Exibe informações/endereço para depósito de GoldStay.
      */
     public function showDepositInfo(): View
     {
         $user = Auth::user()->loadMissing('goldStayWallet');
         $address = $user->goldStayWallet?->polygon_address;
         // TODO: Gerar QR Code para o endereço

         if (!$address) {
             // TODO: Lógica para criar a carteira GoldStay se não existir
             // $address = GoldStayService::createWallet($user);
         }

         return view('dashboard.goldstay.deposit', compact('user', 'address')); // Criar esta view
     }

      /**
       * Exibe formulário para sacar GoldStay para carteira externa.
       */
      public function showWithdrawForm(): View
      {
           $user = Auth::user()->loadMissing('goldStayWallet');
           $balance = $user->goldStayWallet?->balance ?? 0;
           // TODO: Buscar taxa de saque da rede Polygon (via Service/Web3)

           return view('dashboard.goldstay.withdraw', compact('user', 'balance')); // Criar esta view
      }

      /**
       * Processa o saque de GoldStay.
       */
      // public function handleWithdraw(WithdrawGoldStayRequest $request): RedirectResponse { ... }


}