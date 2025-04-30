<?php

namespace App\Http\Requests\Dashboard; // Mover para Dashboard

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check(); // Usuário logado pode atualizar seu perfil
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // Garante que o email seja único, ignorando o próprio usuário
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            // Adicionar validação para phone_number, date_of_birth, address
            'phone_number' => ['nullable', 'string', 'max:30'], // Ajustar regras conforme formato
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:-18 years'], // Ex: maior de 18
            'address' => ['nullable', 'string', 'max:1000'], // Ou 'json' se for JSON
        ];
    }
}