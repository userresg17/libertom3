<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File; // Usar regras de arquivo modernas

class KycSubmitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         // Verifica se o usuário logado pode enviar KYC (não aprovado/pendente)
         $user = Auth::user();
         return $user && !in_array($user->kyc_status, ['approved', 'pending']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Exigir todos os arquivos na primeira submissão
        // Pode ser 'nullable' em cenários de reenvio específico, mas requer lógica extra
        $rule = File::types(['jpg', 'jpeg', 'png', 'webp'])->max(5 * 1024); // Max 5MB

        return [
            'selfie' => ['required', $rule],
            'doc_front' => ['required', $rule],
            'doc_back' => ['required', $rule],
        ];
    }
}