<?php

namespace App\Http\Requests\Dashboard; // Ou Api/V1

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Usar Auth::guard('sanctum') ou JWT na API

class GetTransferQuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         // return Auth::guard('sanctum')->check(); // Exemplo para API Sanctum
         return true; // Ajustar para API
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'source_currency' => ['required', 'string', 'size:3'],
            'destination_currency' => ['required', 'string', 'size:3'],
            'amount_sent' => ['required', 'numeric', 'gt:0'],
        ];
    }
}