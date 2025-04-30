<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\GoldStayController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\SendMoneyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password reset routes
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Wallets
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets');
    Route::get('/wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');
    
    // Investments
    Route::get('/investments', [InvestmentController::class, 'index'])->name('investments');
    Route::get('/investments/{investment}', [InvestmentController::class, 'show'])->name('investments.show');
    
    // GoldStay
    Route::get('/goldstay', [GoldStayController::class, 'index'])->name('goldstay');
    
    // Gift Cards
    Route::get('/giftcards', [GiftCardController::class, 'index'])->name('giftcards');
    Route::get('/giftcards/{giftcard}', [GiftCardController::class, 'show'])->name('giftcards.show');
    
    // Send Money
    Route::get('/sendmoney', [SendMoneyController::class, 'index'])->name('sendmoney');
    Route::post('/sendmoney', [SendMoneyController::class, 'process'])->name('sendmoney.process');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Stripe Payment Processing Routes
    Route::prefix('payment')->group(function () {
        // Deposit Form
        Route::get('/deposit', [StripeController::class, 'showPaymentForm'])->name('stripe.deposit');
        
        // Process Deposit
        Route::post('/process-deposit', [StripeController::class, 'processDeposit'])->name('stripe.process-deposit');
        
        // Authentication
        Route::get('/authenticate', [StripeController::class, 'handleAuthentication'])->name('stripe.handle-auth');
        
        // Return URL
        Route::get('/return', [StripeController::class, 'handlePaymentReturn'])->name('stripe.return');
        
        // Withdrawal
        Route::post('/process-withdrawal', [StripeController::class, 'processWithdrawal'])->name('stripe.process-withdrawal');
        
        // International Transfer
        Route::post('/process-international-transfer', [StripeController::class, 'processInternationalTransfer'])->name('stripe.process-international-transfer');
    });
});

// Webhooks
Route::post('/webhooks/stripe', [StripeController::class, 'handleWebhook'])->name('webhooks.stripe');

// Admin routes
Route::prefix(config('app.admin_path', 'administracao'))->middleware(['auth', 'admin'])->group(function () {
    // Admin routes are defined in admin.php
});
