<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\SendMoneyController; // Exemplo

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Exemplo: Rota de Cotação de Transferência (protegida por Sanctum/JWT)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () { // Ou seu middleware JWT

    Route::post('/transfers/quote', [SendMoneyController::class, 'getQuote'])->name('api.transfers.quote');

    // Outros endpoints de API para o frontend ou apps móveis
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
});


// Endpoints públicos de API (se houver)
// Route::get('/v1/assets/public', [SomeController::class, 'publicAssets']);