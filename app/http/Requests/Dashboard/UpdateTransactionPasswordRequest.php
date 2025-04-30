<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password; // Reutilizar regras de senha

class UpdateTransactionPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = Auth::user();
        $rules = [];

        // Se o usuário JÁ TEM senha de transação, exigir a atual
        if ($user->hasTransactionPassword()) {
             $rules['current_transaction_password'] = ['required', 'string'];
             // Adicionar regra customizada para verificar a senha atual aqui ou no Service
             // Ex: new CurrentTransactionPasswordRule
        }

        // Regras para a nova senha
        $rules['transaction_password'] = [
            'required',
            'string',
            Password::min(6)->numbers()->letters(), // Exemplo de regras - ajuste conforme necessário
            'confirmed' // Requer campo 'transaction_password_confirmation'
        ];

        return $rules;
    }

     /**
      * Mensagens customizadas.
      */
     public function messages(): array
     {
         return [
             'current_transaction_password.required' => 'A senha de transação atual é obrigatória.',
             'transaction_password.required' => 'A nova senha de transação é obrigatória.',
             'transaction_password.min' => 'A senha de transação deve ter pelo menos 6 caracteres.',
             'transaction_password.confirmed' => 'A confirmação da nova senha de transação não confere.',
         ];
     }
}