<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
// use App\Rules\ValidPolygonAddress; // <-- Criar Regra

class WithdrawGoldStayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Verificar se usuário pode sacar GST (KYC, etc)
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0', 'regex:/^\d+(\.\d{1,18})?$/'], // 18 casas decimais
             // TODO: Validar amount contra saldo GST (melhor no Service)
            'recipient_address' => ['required', 'string', 'starts_with:0x', 'size:42', /* new ValidPolygonAddress() */],
            'transaction_password' => ['required', 'string'], // Validar no Service
        ];
    }
}