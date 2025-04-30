<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogService;

class UserService
{
     public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Lista usuários com filtros e paginação para o admin.
     */
    public function listUsers(array $filters): LengthAwarePaginator
    {
        Log::debug('Admin: Listing users', $filters);
        $query = User::query()->with('goldStayWallet');

        // TODO: Implementar lógica de filtros (search, status, etc.)
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('email', 'like', "%{$searchTerm}%"));
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
             // Ajustar se filtro 'pending_kyc' for baseado em kyc_status
        }

        return $query->latest()->paginate(config('libertom.pagination.admin_users', 15))->withQueryString();
    }

    /**
     * Busca dados detalhados de um usuário para edição no admin.
     */
    public function getUserDetailsForEdit(User $user): array
    {
         Log::debug('Admin: Getting user details for edit', ['user_id' => $user->id]);
         $user->loadMissing(['wallets', 'goldStayWallet', 'kycDocuments']);
         // TODO: Buscar histórico ou outros dados agregados se necessário
         return ['user' => $user];
    }

    /**
     * Atualiza dados básicos de um usuário pelo admin.
     */
    public function updateUser(User $user, array $validatedData): User
    {
        Log::info('Admin: Updating user', ['user_id' => $user->id, 'admin_id' => auth('admin')->id()]);
        $oldData = $user->only(array_keys($validatedData)); // Pega dados antigos para log
        $user->update($validatedData);
        $this->auditLogService->log('user_updated', $user, ['old' => $oldData, 'new' => $validatedData]);
        return $user->refresh();
    }

    /**
     * Exclui (Soft Delete) um usuário pelo admin.
     * @throws \Exception Se tentar excluir a si mesmo ou houver regra impeditiva.
     */
    public function deleteUser(User $user): bool
    {
        Log->warning('Admin: Deleting user', ['user_id' => $user->id, 'admin_id' => auth('admin')->id()]);
        // TODO: Adicionar verificação para não excluir a si mesmo (se User e AdminUser forem a mesma tabela/guarda)
        // TODO: Adicionar outras verificações (ex: saldo zerado) se necessário

        $result = $user->delete();
        if ($result) {
            $this->auditLogService->log('user_deleted', $user);
        }
        return $result;
    }

    /**
     * Alterna o status de bloqueio de um usuário.
     */
    public function toggleBlockUser(User $user): string
    {
        $newStatus = $user->is_blocked ? 'active' : 'blocked';
        $action = $newStatus === 'blocked' ? 'user_blocked' : 'user_unblocked';
        Log::info('Admin: Toggling user block status', ['user_id' => $user->id, 'new_status' => $newStatus, 'admin_id' => auth('admin')->id()]);
        $user->update(['status' => $newStatus]);
        $this->auditLogService->log($action, $user);
        return $newStatus;
    }
}