<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\InvestmentController as AdminInvestmentController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
// Importar outros controllers admin

// Ler o prefixo do BD ou config. Default 'administracao'.
// Esta leitura dinâmica PODE precisar ser feita em um RouteServiceProvider.
// Para simplicidade aqui, vamos assumir que 'admin_route_prefix' está em config/app.php
// ou que o RouteServiceProvider define o prefixo globalmente para este arquivo.
// $adminPrefix = config('app.admin_route_prefix', 'administracao');

// Grupo de rotas do Admin
// Aplicar prefixo e middleware aqui
// Route::prefix($adminPrefix)->middleware(['auth:admin', 'can:access_admin_panel'])->name('admin.')->group(function () { // Exemplo completo
Route::middleware(['auth:admin', /* 'can:access_admin_panel' */])->name('admin.')->group(function () { // Simplificado - ajuste middleware conforme necessário

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Usuários (Clientes)
    Route::resource('users', AdminUserController::class)->except(['create', 'store', 'show']); // Não precisamos de create/store separado, show é o edit
    Route::post('users/{user}/toggle-block', [AdminUserController::class, 'toggleBlock'])->name('users.toggleBlock');
    Route::post('users/{user}/adjust-balance', [AdminUserController::class, 'adjustBalance'])->name('users.adjustBalance');
    Route::post('users/{user}/update-kyc', [AdminUserController::class, 'updateKycStatus'])->name('users.updateKycStatus');

    // Ativos de Investimento
    Route::resource('investments', AdminInvestmentController::class)->except(['create', 'show']); // CRUD de Ativos

    // Transações
    Route::get('transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::post('transactions/{transaction}/approve-withdrawal', [AdminTransactionController::class, 'approveWithdrawal'])->name('transactions.approveWithdrawal');
    Route::post('transactions/{transaction}/reject-withdrawal', [AdminTransactionController::class, 'rejectWithdrawal'])->name('transactions.rejectWithdrawal');
    // Adicionar rota para ver detalhes de uma transação se necessário

    // Configurações
    Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::put('settings/general', [AdminSettingController::class, 'updateGeneral'])->name('settings.updateGeneral');
    Route::put('settings/admin-url', [AdminSettingController::class, 'updateAdminUrl'])->name('settings.updateAdminUrl');
    Route::put('settings/gateways', [AdminSettingController::class, 'updateGateways'])->name('settings.updateGateways');
    Route::put('settings/withdrawals', [AdminSettingController::class, 'updateWithdrawals'])->name('settings.updateWithdrawals');

    // TODO: Adicionar rotas para Gerenciamento de Funcionários e ACL se necessário

}); // Fim do grupo admin

// TODO: Adicionar rotas de login específicas para admin se não usar o guard padrão 'web'
// Exemplo: Route::prefix($adminPrefix)->group(function() { require __DIR__.'/auth_admin.php'; });