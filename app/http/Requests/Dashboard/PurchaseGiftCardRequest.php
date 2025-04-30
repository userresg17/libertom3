<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseGiftCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Verificar se usuário pode comprar gift cards
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'currency' => ['required', 'string', 'size:3', 'in:USD,BRL,EUR'], // Moedas permitidas para GC
            'amount' => ['required', 'numeric', 'integer', 'min:10'], // Ex: valor inteiro mínimo 10
            'payment_wallet_id' => ['required', 'integer', 'exists:wallets,id,user_id,' . Auth::id()],
             // TODO: Validar se 'currency' da carteira é compatível com 'currency' do GC ou se há saldo suficiente após conversão (melhor no Service)
            'recipient_email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:500'],
            'transaction_password' => ['required', 'string'], // Validar no Service
        ];
    }
}