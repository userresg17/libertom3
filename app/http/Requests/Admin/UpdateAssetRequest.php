<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Exemplo: return Auth::guard('admin')->user()->can('edit assets');
        return Auth::guard('admin')->check(); // Ajustar permissão!
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
         $assetId = $this->route('asset')->id; // Pega o ID do ativo da rota

         return [
             'name' => 'required|string|max:255',
             // Permite atualizar símbolo, mas deve continuar único, ignorando o próprio ID
             'symbol' => 'required|string|max:20|unique:assets,symbol,' . $assetId,
             'description' => 'nullable|string|max:2000',
             'type' => ['required', 'string', Rule::in(['stock', 'etf', 'bond'])],
             'current_price' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,8})?$/',
             'operating_fee' => 'nullable|numeric|min:0|max:100|regex:/^\d+(\.\d{1,4})?$/',
             'logo_url' => 'nullable|url|max:2048',
             // 'logo_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
             'rules' => 'nullable|array',
             'rules.*.time' => 'nullable|required_with:rules.*.percentage_change|date_format:H:i',
             'rules.*.percentage_change' => ['nullable', 'required_with:rules.*.time', 'numeric', 'regex:/^-?\d+(\.\d{1,4})?$/'],
             'is_active' => 'nullable|boolean',
         ];
    }
}