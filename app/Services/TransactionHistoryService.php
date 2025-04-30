<?php
namespace App\Services; // Ou App\Services\Dashboard

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class TransactionHistoryService
{
    /**
     * Retorna o histórico paginado de transações de um usuário com filtros.
     */
    public function getUserHistory(User $user, array $filters): array
    {
        Log::debug("Getting transaction history for user", ['user_id' => $user->id, 'filters' => $filters]);
        $query = Transaction::where('user_id', $user->id)
                             ->with(['asset:id,symbol', 'wallet:id,currency']); // Eager load leve

        // TODO: Aplicar filtros (type, status, date_from, date_to, currency/asset)
        if (!empty($filters['type'])) { $query->where('type', $filters['type']); }
        if (!empty($filters['status'])) { $query->where('status', $filters['status']); }
        if (!empty($filters['date_from'])) { $query->whereDate('created_at', '>=', $filters['date_from']); }
        if (!empty($filters['date_to'])) { $query->whereDate('created_at', '<=', $filters['date_to']); }
         // Adicionar filtro por moeda (wallet.currency) ou ativo (asset.symbol) aqui se necessário

        $transactions = $query->latest()->paginate(config('libertom.pagination.transaction_history', 25))->withQueryString();

        // TODO: Obter lista de tipos de transação para o filtro da view
        $transactionTypes = config('libertom.transaction_types', []); // Definir isso em config/libertom.php

        return compact('transactions', 'transactionTypes');
    }
}