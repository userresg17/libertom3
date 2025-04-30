<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class AdjustBalanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Exemplo: return Auth::guard('admin')->user()->can('adjust balances');
        return Auth::guard('admin')->check(); // Ajustar permissão!
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Validar que pelo menos um ajuste (fiat ou gst) foi enviado
         $rules = [
             'fiat_currency' => 'nullable|required_with:fiat_amount|string|size:3', // Ex: USD, BRL
             'fiat_amount' => ['nullable', 'required_with:fiat_currency', 'numeric', 'regex:/^-?\d+(\.\d{1,8})?$/'], // Permite negativo, até 8 decimais
             'fiat_reason' => 'nullable|required_with:fiat_amount|string|max:255',

             'gst_amount' => ['nullable', 'required_without:fiat_amount', 'numeric', 'regex:/^-?\d+(\.\d{1,18})?$/'], // Permite negativo, alta precisão
             'gst_reason' => 'nullable|required_with:gst_amount|string|max:255',
         ];

         // Garantir que pelo menos um valor foi preenchido
         $rules['fiat_amount'][] = 'required_without:gst_amount';

         return $rules;
    }

     /**
      * Mensagens customizadas de erro.
      */
     public function messages(): array
     {
         return [
             'fiat_amount.required_without' => 'É necessário informar um valor para ajuste (Fiat ou GST).',
             'gst_amount.required_without' => 'É necessário informar um valor para ajuste (Fiat ou GST).',
         ];
     }
}