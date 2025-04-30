<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Para verificar o admin logado

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Implementar lógica de permissão aqui (ex: Spatie).
     */
    public function authorize(): bool
    {
        // Exemplo: return Auth::guard('admin')->user()->can('edit users');
        return Auth::guard('admin')->check(); // Permite qualquer admin logado (ajustar!)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id; // Pega o ID do usuário da rota

        return [
            'name' => 'required|string|max:255',
            // Não validar email aqui se não for permitido alterar
            // 'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'status' => 'required|string|in:active,blocked,pending_kyc', // Validar os status permitidos
            // Adicionar validação para outros campos do perfil se houver (telefone, etc.)
        ];
    }
}