<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// TODO: Importar Services se necessário (ex: para aprovar/rejeitar saques)
// TODO: Importar AuditLog Service

class TransactionController extends Controller
{
    /**
     * Listar todas as transações com filtros.
     */
    public function index(Request $request): View
    {
         $query = Transaction::with(['user', 'asset', 'wallet']); // Eager load

         // Aplicar filtros (user_search, type, status, date_from, date_to)
         // ... lógica de filtro similar ao UserController ...
         if ($request->filled('user_search')) { /* ... */ }
         if ($request->filled('type')) { $query->where('type', $request->input('type')); }
         if ($request->filled('status')) { $query->where('status', $request->input('status')); }
         if ($request->filled('date_from')) { $query->whereDate('created_at', '>=', $request->input('date_from')); }
         if ($request->filled('date_to')) { $query->whereDate('created_at', '<=', $request->input('date_to')); }


         $transactions = $query->latest()->paginate(25)->withQueryString();

         return view('admin.transactions.index', compact('transactions')); // Ajustar caminho: admin.transactions
    }

    /**
     * Aprovar um saque pendente (via AJAX/Fetch).
     */
    public function approveWithdrawal(Transaction $transaction): \Illuminate\Http\JsonResponse // Ou RedirectResponse
    {
         // Verificar se é um saque e se está pendente
         if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
             return response()->json(['success' => false, 'message' => 'Transação inválida para aprovação.'], 400);
         }
         // TODO: Verificar permissões do admin
         // TODO: Chamar um Service para processar a aprovação (ex: interagir com gateway de pagamento)
         // $success = WithdrawalService::approve($transaction);

         $success = true; // Simulação

         if ($success) {
             $transaction->update(['status' => 'processing']); // Ou 'completed' se o service já confirmar
             // TODO: Logar ação
             // AuditLogService::log('withdrawal_approved', $transaction, [], auth('admin')->user());
             return response()->json(['success' => true]);
         } else {
              // $transaction->update(['status' => 'failed', 'metadata' => array_merge($transaction->metadata ?? [], ['fail_reason' => 'Admin Approval Failed'])]);
              // Logar falha
              return response()->json(['success' => false, 'message' => 'Falha ao aprovar saque.'], 500);
         }
    }

    /**
     * Rejeitar um saque pendente (via AJAX/Fetch).
     */
    public function rejectWithdrawal(Request $request, Transaction $transaction): \Illuminate\Http\JsonResponse // Ou RedirectResponse
    {
        // Verificar se é um saque e se está pendente
         if ($transaction->type !== 'withdrawal' || $transaction->status !== 'pending') {
             return response()->json(['success' => false, 'message' => 'Transação inválida para rejeição.'], 400);
         }
        // TODO: Verificar permissões do admin
        // TODO: Chamar um Service para processar a rejeição (ex: estornar fundos se já reservados)
         // $reason = $request->input('reason', 'Rejeitado pelo administrador'); // Opcional: pegar motivo do request
         // $success = WithdrawalService::reject($transaction, $reason);

         $success = true; // Simulação

          if ($success) {
              $transaction->update(['status' => 'rejected', 'metadata' => array_merge($transaction->metadata ?? [], ['rejection_reason' => 'Admin Rejected'])]);
               // TODO: Logar ação
              // AuditLogService::log('withdrawal_rejected', $transaction, ['reason' => $reason], auth('admin')->user());
              return response()->json(['success' => true]);
          } else {
               // Logar falha
               return response()->json(['success' => false, 'message' => 'Falha ao rejeitar saque.'], 500);
          }
    }
}