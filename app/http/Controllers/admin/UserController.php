<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet; // Necessário para ajuste de saldo
use App\Models\GoldStayWallet; // Necessário para ajuste de saldo
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log; // Para logs de auditoria
use Illuminate\Support\Facades\DB; // Para transações de banco de dados
use App\Http\Requests\Admin\UpdateUserRequest; // <-- Criar este Form Request
use App\Http\Requests\Admin\AdjustBalanceRequest; // <-- Criar este Form Request
use App\Http\Requests\Admin\UpdateKycStatusRequest; // <-- Criar este Form Request
// TODO: Importar AuditLog Model ou Service

class UserController extends Controller
{
    /**
     * Listar usuários com filtros e paginação.
     */
    public function index(Request $request): View
    {
        $query = User::query()->with('goldStayWallet'); // Eager load para mostrar saldo GST

        // Aplicar filtros
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('status')) {
             // Mapear status do filtro para status do banco se necessário
             $dbStatus = $request->input('status'); // Ex: 'active', 'blocked', 'pending_kyc'
             if ($dbStatus === 'pending_kyc') {
                // Se 'pending_kyc' for um status real na tabela users
                $query->where('status', $dbStatus);
                // Ou se for baseado no kyc_status
                // $query->where('kyc_status', 'pending')->orWhere('kyc_status', 'resubmission_requested');
             } else {
                 $query->where('status', $dbStatus);
             }
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users')); // Ajustar caminho se necessário: admin.users
    }

    /**
     * Exibir formulário de edição do usuário.
     */
    public function edit(User $user): View
    {
        $user->load(['wallets', 'goldStayWallet', 'kycDocuments']); // Carregar dados relacionados
        // TODO: Carregar histórico de transações/atividades se for exibido na tab 'history'

        return view('admin.users.edit', compact('user')); // Ajustar caminho se necessário: admin.edit-user
    }

    /**
     * Atualizar dados do usuário (nome, status, etc.).
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        // UpdateUserRequest cuidará da validação
        $validated = $request->validated();

        // TODO: Logar ação de auditoria ANTES de salvar
        // AuditLogService::log('user_updated', $user, $validated, auth('admin')->user());

        $user->update($validated);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Excluir (Soft Delete) um usuário.
     */
    public function destroy(User $user): RedirectResponse
    {
        // TODO: Adicionar verificação de permissão (ex: if (!auth('admin')->user()->can('delete users'))) abort(403);
        // TODO: Logar ação de auditoria
        // AuditLogService::log('user_deleted', $user, [], auth('admin')->user());

        // Verificar se o usuário a ser excluído não é o próprio admin logado (importante!)
        if (auth('admin')->id() === $user->id && $user instanceof \App\Models\AdminUser) { // Comparação segura
             return back()->with('error', 'Você não pode excluir sua própria conta de administrador.');
        }

        $user->delete(); // Soft delete

        return redirect()->route('admin.users.index')->with('success', 'Usuário excluído com sucesso.');
    }

    /**
     * Bloquear/Desbloquear usuário (via AJAX/Fetch).
     */
    public function toggleBlock(User $user): \Illuminate\Http\JsonResponse
    {
         // TODO: Adicionar verificação de permissão

        $newStatus = $user->status === 'blocked' ? 'active' : 'blocked';
        $action = $newStatus === 'blocked' ? 'user_blocked' : 'user_unblocked';

         // TODO: Logar ação de auditoria
         // AuditLogService::log($action, $user, ['new_status' => $newStatus], auth('admin')->user());

        $user->update(['status' => $newStatus]);

        return response()->json(['success' => true, 'new_status' => $newStatus]);
    }

    /**
     * Ajustar saldo manualmente (Fiat ou GoldStay).
     */
    public function adjustBalance(AdjustBalanceRequest $request, User $user): RedirectResponse
    {
        // AdjustBalanceRequest cuidará da validação
        $validated = $request->validated();
        $adminUser = auth('admin')->user();

        DB::beginTransaction();
        try {
            // Ajuste Fiat
            if (!empty($validated['fiat_currency']) && !empty($validated['fiat_amount'])) {
                $wallet = $user->wallets()->firstOrCreate(
                    ['currency' => $validated['fiat_currency']],
                    ['balance' => 0, 'decimal_places' => ($validated['fiat_currency'] === 'JPY' ? 0 : 2)] // Define decimal places
                );
                $amount = (float) $validated['fiat_amount'];
                $oldBalance = $wallet->balance;
                $wallet->balance += $amount; // Pode ser negativo
                $wallet->save();

                // Criar transação de ajuste
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'type' => 'admin_adjustment',
                    'status' => 'completed',
                    'currency' => $wallet->currency,
                    'amount' => $amount,
                    'description' => 'Ajuste Admin: ' . $validated['fiat_reason'],
                    'metadata' => ['admin_id' => $adminUser->id],
                    'balance_before' => $oldBalance,
                    'balance_after' => $wallet->balance,
                ]);
                 // TODO: Logar ação de auditoria detalhada
                 // AuditLogService::log('fiat_balance_adjusted', $user, $validated, $adminUser);
            }

             // Ajuste GoldStay
             if (!empty($validated['gst_amount'])) {
                 $gstWallet = $user->goldStayWallet()->firstOrCreate(['polygon_address' => 'TBD']); // Endereço precisa ser gerenciado
                 $gstAmount = (float) $validated['gst_amount']; // Usar precisão alta aqui
                 $oldGstBalance = $gstWallet->balance;
                 $gstWallet->balance += $gstAmount; // Pode ser negativo
                 $gstWallet->save();

                 // Criar transação de ajuste GST
                Transaction::create([
                    'user_id' => $user->id,
                    'wallet_id' => null, // Não é uma carteira fiat
                    'type' => 'admin_adjustment_gst', // Tipo específico
                    'status' => 'completed',
                    'currency' => 'GST', // Usar GST como 'moeda'
                    'amount' => $gstAmount, // Quantidade de GST
                    'description' => 'Ajuste Admin GST: ' . $validated['gst_reason'],
                    'metadata' => ['admin_id' => $adminUser->id],
                     // Saldo GST pode precisar de campos diferentes ou ir para metadata
                     // 'balance_before' => $oldGstBalance,
                     // 'balance_after' => $gstWallet->balance,
                ]);

                // TODO: Logar ação de auditoria detalhada
                // AuditLogService::log('gst_balance_adjusted', $user, $validated, $adminUser);
             }

            DB::commit();
            return redirect()->route('admin.users.edit', $user)->with('success', 'Saldos ajustados com sucesso.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao ajustar saldo para user ID {$user->id}: " . $e->getMessage());
            return back()->with('error', 'Erro ao ajustar saldos. Tente novamente.');
        }
    }

    /**
     * Atualizar status do KYC.
     */
    public function updateKycStatus(UpdateKycStatusRequest $request, User $user): RedirectResponse
    {
        // UpdateKycStatusRequest valida 'status' e 'reason' (se rejeitado/reenvio)
        $validated = $request->validated();

        $oldStatus = $user->kyc_status;
        $newStatus = $validated['status']; // 'approved', 'rejected', 'resubmission_requested'

        // TODO: Logar ação de auditoria
        // AuditLogService::log('kyc_status_updated', $user, $validated, auth('admin')->user());

        $updateData = ['kyc_status' => $newStatus];
        if (($newStatus === 'rejected' || $newStatus === 'resubmission_requested') && !empty($validated['reason'])) {
             $updateData['kyc_rejection_reason'] = $validated['reason'];
        } else {
             $updateData['kyc_rejection_reason'] = null; // Limpa razão se aprovado
        }

         // Atualiza status do usuário também se necessário
        // if ($newStatus === 'approved' && $user->status === 'pending_kyc') {
        //     $updateData['status'] = 'active';
        // }

        $user->update($updateData);

        // TODO: Enviar notificação para o usuário sobre a mudança de status do KYC

        return redirect()->route('admin.users.edit', $user)->with('success', 'Status KYC atualizado com sucesso.');
    }
}