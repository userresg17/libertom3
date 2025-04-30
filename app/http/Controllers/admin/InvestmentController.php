<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use Illuminate\Http\Request; // Usar FormRequest depois
use App\Http\Requests\Admin\StoreAssetRequest; // <-- Criar
use App\Http\Requests\Admin\UpdateAssetRequest; // <-- Criar
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// TODO: Importar AuditLog Service

class InvestmentController extends Controller
{
    /**
     * Listar ativos de investimento gerenciáveis.
     */
    public function index(Request $request): View
    {
        $query = Asset::query();
        // Add filters if needed (e.g., by type, status)
        $assets = $query->latest()->paginate(20);
        return view('admin.investments.index', compact('assets')); // Ajustar caminho: admin.investments
    }

    /**
     * Armazenar um novo ativo. (Chamado pelo modal/formulário)
     */
    public function store(StoreAssetRequest $request): RedirectResponse // Ou JsonResponse se usar fetch
    {
        $validated = $request->validated();

        // TODO: Processar upload de logo se houver
        // if ($request->hasFile('logo')) { ... $validated['logo_url'] = $path; }

        // TODO: Logar ação
        // AuditLogService::log('asset_created', null, $validated, auth('admin')->user());

        Asset::create($validated);

        // Se for fetch, retornar JSON
        // return response()->json(['success' => true, 'message' => 'Ativo criado com sucesso.']);

        return redirect()->route('admin.investments.index')->with('success', 'Ativo criado com sucesso.');
    }

    /**
     * Exibir formulário de edição (pode ser o mesmo modal preenchido).
     * Normalmente não é necessário um método 'edit' separado se o modal busca dados via API ou Alpine.
     * Se precisar de uma página separada:
     */
    // public function edit(Asset $asset): View
    // {
    //     return view('admin.investments.edit', compact('asset'));
    // }

    /**
     * Atualizar um ativo existente. (Chamado pelo modal/formulário)
     */
    public function update(UpdateAssetRequest $request, Asset $asset): RedirectResponse // Ou JsonResponse
    {
        $validated = $request->validated();

         // TODO: Processar upload de logo se houver nova imagem

         // TODO: Logar ação
         // AuditLogService::log('asset_updated', $asset, $validated, auth('admin')->user());

        $asset->update($validated);

         // Se for fetch, retornar JSON
         // return response()->json(['success' => true, 'message' => 'Ativo atualizado com sucesso.']);

        return redirect()->route('admin.investments.index')->with('success', 'Ativo atualizado com sucesso.');
    }

    /**
     * Excluir (Soft Delete) um ativo.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
         // TODO: Verificar permissões
         // TODO: Logar ação
         // AuditLogService::log('asset_deleted', $asset, [], auth('admin')->user());

        $asset->delete();

        return redirect()->route('admin.investments.index')->with('success', 'Ativo excluído com sucesso.');
    }
}