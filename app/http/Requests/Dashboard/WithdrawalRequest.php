<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequest extends FormRequest
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
        return [
            'wallet_id' => ['required', 'integer', 'exists:wallets,id,user_id,' . Auth::id()], // Garante que a carteira pertence ao usuário
            'amount' => ['required', 'numeric', 'gt:0'], // Maior que zero
            'method' => ['required', 'string'], // Ex: 'pix', 'ted', 'stripe_payout', 'swift'
            // Adicionar regras dinâmicas baseadas no 'method' para os detalhes da conta de destino
            'recipient_details' => ['required', 'array'],
            'recipient_details.account_number' => ['required_if:method,ted,swift', 'string', /*...*/],
            'recipient_details.iban' => ['required_if:method,swift', 'string', /*...*/],
            'recipient_details.pix_key' => ['required_if:method,pix', 'string', /*...*/],
            // ... etc ...
            'transaction_password' => ['required', 'string'], // Validar com regra customizada no Service
        ];
        // TODO: Adicionar validação para garantir que o valor não excede o saldo da carteira (melhor no Service)
    }
}