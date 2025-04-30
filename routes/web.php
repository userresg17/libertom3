<?php

use App\Http\Controllers\ProfileController; // Controller padrão do Breeze/Jetstream
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\WalletController;
use App\Http\Controllers\Dashboard\InvestmentController as DashboardInvestmentController;
use App\Http\Controllers\Dashboard\GoldStayController;
use App\Http\Controllers\Dashboard\GiftCardController;
use App\Http\Controllers\Dashboard\SendMoneyController;
use App\Http\Controllers\Dashboard\TransactionHistoryController;
use App\Http\Controllers\Dashboard\ProfileController as DashboardProfileController; // Nosso controller customizado
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rotas Públicas (Exemplo)
Route::get('/', function () {
    // return view('welcome'); // Landing Page padrão do Laravel
    return view('landing'); // Sua landing page customizada
})->name('landing');

Route::get('/termos-de-uso', function () {
    return view('terms-and-conditions'); // Sua página de termos
})->name('terms');

// Rotas de Autenticação (Gerenciadas pelo Breeze/Fortify/UI)
// Se estiver usando Breeze, essas rotas já são definidas por ele.
// Exemplo se precisar definir manualmente:
// require __DIR__.'/auth.php'; // Arquivo de rotas de autenticação do Breeze/Fortify


// --- Rotas do Dashboard do Cliente (Protegidas) ---
Route::middleware(['auth', 'verified'])->group(function () { // 'auth' usa o guard padrão 'web'

    // Dashboard Principal
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil (Combina Breeze/Fortify com nosso controller)
    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); // Rota padrão Breeze
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Rota padrão Breeze
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); // Rota padrão Breeze

    // Usando nosso ProfileController customizado para unificar
    Route::get('/profile', [DashboardProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/details', [DashboardProfileController::class, 'update'])->name('profile.update'); // Rota para dados pessoais
    Route::post('/profile/kyc', [DashboardProfileController::class, 'submitKyc'])->name('profile.kyc.submit'); // Rota para upload KYC
    Route::put('/profile/transaction-password', [DashboardProfileController::class, 'updateTransactionPassword'])->name('profile.transactionPassword.update'); // Rota para senha de transação
    // NOTA: A atualização da senha de LOGIN geralmente é tratada por uma rota/controller separado no Breeze/Fortify (`password.update`)


    // Carteiras (Wallets)
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/deposit', [WalletController::class, 'showDepositForm'])->name('wallets.deposit');
    // Route::post('/wallets/deposit', [WalletController::class, 'handleDeposit'])->name('wallets.deposit.store');
    Route::get('/wallets/withdraw', [WalletController::class, 'showWithdrawForm'])->name('wallets.withdraw');
    // Route::post('/wallets/withdraw', [WalletController::class, 'handleWithdrawal'])->name('wallets.withdraw.store');
    Route::get('/wallets/convert', [WalletController::class, 'showConvertForm'])->name('wallets.convert');
    // Route::post('/wallets/convert', [WalletController::class, 'handleConversion'])->name('wallets.convert.store');


    // Investimentos
    Route::get('/investments', [DashboardInvestmentController::class, 'index'])->name('investments.index');
    Route::get('/investments/discover', [DashboardInvestmentController::class, 'discover'])->name('investments.discover');
    Route::get('/investments/asset/{asset:symbol}', [DashboardInvestmentController::class, 'assetDetails'])->name('investments.assetDetails'); // Usando route model binding com symbol
    Route::get('/investments/trade/{asset:symbol}', [DashboardInvestmentController::class, 'showTradeForm'])->name('investments.trade');
    // Route::post('/investments/trade/{asset:symbol}', [DashboardInvestmentController::class, 'handleTradeOrder'])->name('investments.trade.store');


    // GoldStay
    Route::get('/goldstay', [GoldStayController::class, 'index'])->name('goldstay.index');
    // Route::get('/goldstay/buy', [GoldStayController::class, 'showBuyForm'])->name('goldstay.buy');
    // Route::post('/goldstay/buy', [GoldStayController::class, 'handleBuy'])->name('goldstay.buy.store');
    // Route::get('/goldstay/sell', [GoldStayController::class, 'showSellForm'])->name('goldstay.sell');
    // Route::post('/goldstay/sell', [GoldStayController::class, 'handleSell'])->name('goldstay.sell.store');
    Route::get('/goldstay/deposit', [GoldStayController::class, 'showDepositInfo'])->name('goldstay.deposit');
    Route::get('/goldstay/withdraw', [GoldStayController::class, 'showWithdrawForm'])->name('goldstay.withdraw');
    // Route::post('/goldstay/withdraw', [GoldStayController::class, 'handleWithdraw'])->name('goldstay.withdraw.store');


    // Gift Cards
    Route::get('/giftcards', [GiftCardController::class, 'index'])->name('giftcards.index');
    // Route::post('/giftcards/purchase', [GiftCardController::class, 'purchase'])->name('giftcards.purchase');
    // Route::post('/giftcards/redeem', [GiftCardController::class, 'redeem'])->name('giftcards.redeem');


    // Enviar Dinheiro (Transferências)
    Route::get('/send-money', [SendMoneyController::class, 'index'])->name('sendmoney.index');
    // Route::post('/send-money/quote', [SendMoneyController::class, 'getQuote'])->name('sendmoney.quote'); // MOVER PARA API.PHP se precisar ser stateless/AJAX puro
    // Route::post('/send-money', [SendMoneyController::class, 'submitTransfer'])->name('sendmoney.submit');


    // Histórico de Transações
    Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.history');

}); // Fim do grupo 'auth', 'verified'