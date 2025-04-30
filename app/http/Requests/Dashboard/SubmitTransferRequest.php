<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmitTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Verificar se usuário pode fazer transferências (KYC, etc)
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Estas regras precisam incluir TODOS os campos do formulário,
        // incluindo os campos dinâmicos de destinatário (iban, swift, etc.)
        // e o quote_id obtido anteriormente.
        return [
            'source_wallet_id' => ['required', 'integer', 'exists:wallets,id,user_id,' . Auth::id()],
            'amount_sent' => ['required', 'numeric', 'gt:0'],
             // TODO: Validar amount contra saldo (melhor no Service)
            'destination_country' => ['required', 'string', 'size:2'], // Código ISO do país
            'destination_currency' => ['required', 'string', 'size:3'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'quote_id' => ['required', 'string'], // ID da cotação obtida
            'transaction_password' => ['required', 'string'], // Validar no Service

            // Campos Dinâmicos (Exemplos - precisam ser validados com base no país/moeda)
            'recipient_iban' => ['nullable', 'required_if:destination_country,DE,GB,FR', /* Regra IBAN */ ],
            'recipient_swift' => ['nullable', 'required_if:destination_country,US,GB', /* Regra SWIFT */ ],
            'recipient_account_number' => ['nullable', 'required_if:destination_country,US,BR', /*...*/],
            'recipient_cbu' => ['nullable', 'required_if:destination_country,AR', /*...*/],

            'transfer_purpose' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
        ];
        // TODO: Adicionar lógica complexa para validar campos dinâmicos aqui ou no Service.
    }
}