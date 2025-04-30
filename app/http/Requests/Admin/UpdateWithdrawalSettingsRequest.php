<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateWithdrawalSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         // TODO: Implementar verificação de permissão (ex: 'update withdrawal settings')
         return Auth::guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'withdrawal_auto_approval' => ['nullable', 'boolean'],
            'withdrawal_auto_approval_limit_usd' => [
                'nullable',
                'required_if:withdrawal_auto_approval,true',
                'numeric',
                'min:0',
                'regex:/^\d+(\.\d{1,2})?$/' // Permitir até 2 casas decimais para limite USD
            ],
            // Adicionar outras regras para configurações de saque aqui
        ];
    }
}