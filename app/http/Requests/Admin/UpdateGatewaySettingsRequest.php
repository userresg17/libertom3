<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateGatewaySettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Implementar verificação de permissão (ex: 'update gateway settings')
        return Auth::guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Cora
            'cora_enabled' => ['nullable', 'boolean'],
            'cora_client_id' => ['nullable', 'string', 'max:255', 'required_if:cora_enabled,true'],
            'cora_client_secret' => ['nullable', 'string', 'max:255'], // Não obrigatório para não forçar alteração

            // Stripe
            'stripe_enabled' => ['nullable', 'boolean'],
            'stripe_key' => ['nullable', 'string', 'max:255', 'required_if:stripe_enabled,true'],
            'stripe_secret' => ['nullable', 'string', 'max:255'],
            'stripe_webhook_secret' => ['nullable', 'string', 'max:255'],

            // Adicionar validações para outros gateways se houver
        ];
    }
}