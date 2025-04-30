<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Transaction;

class TransactionHistoryController extends Controller
{
    /**
     * Exibe o histórico completo de transações do usuário com filtros.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Transaction::where('user_id', $user->id)->with(['asset', 'wallet']); // Eager load

        // Aplicar filtros (type, status, date_from, date_to, currency/asset)
        if ($request->filled('type')) { $query->where('type', $request->input('type')); }
        if ($request->filled('status')) { $query->where('status', $request->input('status')); }
        if ($request->filled('date_from')) { $query->whereDate('created_at', '>=', $request->input('date_from')); }
        if ($request->filled('date_to')) { $query->whereDate('created_at', '<=', $request->input('date_to')); }
        // TODO: Adicionar filtro por moeda ou ativo se necessário

        $transactions = $query->latest()->paginate(25)->withQueryString();

        // TODO: Passar lista de tipos de transação para o dropdown de filtro
        $transactionTypes = [ /* ... */ ];

        return view('dashboard.transaction-history', compact('transactions', 'transactionTypes')); // Criar esta view
    }
}