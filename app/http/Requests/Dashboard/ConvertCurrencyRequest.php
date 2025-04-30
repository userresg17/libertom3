<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ConvertCurrencyRequest extends FormRequest
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
            'from_wallet_id' => ['required', 'integer', 'exists:wallets,id,user_id,' . Auth::id()],
            'to_currency' => ['required', 'string', 'size:3', 'different:from_currency'], // Garantir que to_currency é diferente da moeda de from_wallet_id
            'amount_from' => ['required', 'numeric', 'gt:0'],
            // TODO: Validar 'amount_from' contra saldo da carteira (melhor no Service)
            // 'transaction_password' => ['required', 'string'], // Se necessário para conversão
        ];
    }

    /**
     * Prepare the data for validation.
     * Adiciona from_currency para validação 'different'
     */
    protected function prepareForValidation(): void
    {
        $fromWallet = Auth::user()->wallets()->find($this->input('from_wallet_id'));
        $this->merge([
            'from_currency' => $fromWallet?->currency,
        ]);
    }
}