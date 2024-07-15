<?php

namespace App\Http\Requests\client;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "clientName" => "required",
            "email" => ['required', 'email', Rule::unique('client')->ignore(auth('client-api')->user()->clientId, 'clientId')],
            "password" => "required",
            "clientPhoneNumber" => "required",
        ];
    }
}
