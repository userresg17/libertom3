<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateKycStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
         // Exemplo: return Auth::guard('admin')->user()->can('manage kyc');
         return Auth::guard('admin')->check(); // Ajustar permissão!
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['approved', 'rejected', 'resubmission_requested'])],
            'reason' => ['nullable', 'required_if:status,rejected,resubmission_requested', 'string', 'max:1000'],
        ];
    }
}