<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Rules\ValidAdminRoutePrefix; // <-- Criar esta Regra customizada

class UpdateAdminUrlRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar verificação de permissão (ex: 'update admin url')
        return Auth::guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'admin_url_path' => [
                'required',
                'string',
                'alpha_dash', // Permite letras, números, traços e underscores
                'max:100',
                'min:5', // Mínimo para alguma segurança por obscuridade
                new ValidAdminRoutePrefix(), // Regra customizada para evitar conflitos/palavras reservadas
            ],
        ];
    }
}