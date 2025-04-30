<?php
namespace App\Services\Admin;

use App\Models\Asset;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\AuditLogService;

class InvestmentAssetService
{
     public function __construct(private readonly AuditLogService $auditLogService) {}

    /**
     * Lista ativos de investimento com filtros e paginação.
     */
    public function listAssets(array $filters): LengthAwarePaginator
    {
         Log::debug('Admin: Listing assets', $filters);
         $query = Asset::query();
         // TODO: Aplicar filtros (type, status, search)
         return $query->latest()->paginate(config('libertom.pagination.admin_assets', 20));
    }

    /**
     * Cria um novo ativo de investimento.
     */
    public function createAsset(array $validatedData): Asset
    {
        Log::info('Admin: Creating new asset', ['admin_id' => auth('admin')->id()]);
        // TODO: Lógica de upload de logo (se houver)
        // if(isset($validatedData['logo_file'])) { /* ... store ... $validatedData['logo_url'] = ... */ }

        $asset = Asset::create($validatedData);
        $this->auditLogService->log('asset_created', $asset, $validatedData);
        return $asset;
    }

    /**
     * Atualiza um ativo de investimento existente.
     */
    public function updateAsset(Asset $asset, array $validatedData): Asset
    {
         Log::info('Admin: Updating asset', ['asset_id' => $asset->id, 'admin_id' => auth('admin')->id()]);
         // TODO: Lógica de upload/delete de logo (se houver)
         // $oldData = $asset->only(array_keys($validatedData));

         $asset->update($validatedData);
         $this->auditLogService->log('asset_updated', $asset, ['new' => $validatedData /*, 'old' => $oldData */]);
         return $asset->refresh();
    }

    /**
     * Exclui (Soft Delete) um ativo de investimento.
     */
    public function deleteAsset(Asset $asset): bool
    {
        Log::warning('Admin: Deleting asset', ['asset_id' => $asset->id, 'admin_id' => auth('admin')->id()]);
        // TODO: Adicionar verificações se necessário

        $result = $asset->delete();
        if ($result) {
            $this->auditLogService->log('asset_deleted', $asset);
        }
        return $result;
    }
}