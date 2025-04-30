<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Exemplo: return Auth::guard('admin')->user()->can('create assets');
        return Auth::guard('admin')->check(); // Ajustar permissão!
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20|unique:assets,symbol', // Símbolo deve ser único
            'description' => 'nullable|string|max:2000',
            'type' => ['required', 'string', Rule::in(['stock', 'etf', 'bond'])], // Validar tipos permitidos
            'current_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,8})?$/', // 8 casas decimais
            'operating_fee' => 'nullable|numeric|min:0|max:100|regex:/^\d+(\.\d{1,4})?$/', // 4 casas decimais
            'logo_url' => 'nullable|url|max:2048', // Ou validação de 'image' se for upload
            // 'logo_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024', // Exemplo se for upload
            'rules' => 'nullable|array', // Valida que é um array
            'rules.*.time' => 'nullable|required_with:rules.*.percentage_change|date_format:H:i', // Valida cada item do array
            'rules.*.percentage_change' => ['nullable', 'required_with:rules.*.time', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'], // 4 decimais
            'is_active' => 'nullable|boolean',
        ];
    }
}