<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TradeOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Verificar se usuário pode negociar (KYC, etc) e se o ativo é negociável
        return Auth::check() && $this->route('asset')?->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // As regras podem variar se for ordem a mercado, limitada, etc.
        // Exemplo para ordem a mercado:
        return [
            'action' => ['required', 'string', Rule::in(['buy', 'sell'])],
            'quantity' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,8})?$/'], // 8 casas decimais para quantidade
            // Para 'sell', validar se a quantidade não excede a posse (melhor no Service)
            'payment_wallet_currency' => ['required_if:action,buy', 'string', 'size:3', 'in:USD'], // Ex: Apenas compra com USD
            'transaction_password' => ['required', 'string'], // Validar no Service
        ];
    }
}