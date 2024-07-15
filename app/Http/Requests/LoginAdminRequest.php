<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginAdminRequest extends FormRequest
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
            'email' => 'required', 'exists:websiteadmin', 'email',
            'password' => "required"
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'The email field is required.',
            'email.exists' => 'The email does not exist.',
            'password.required' => 'The password field is required.',
        ];
    }
    public function response(array $errors)
    {
        if ($this->expectsJson()) {
            return response()->json(['error' => 'Invalid data', 'errors' => $errors], 422);
        }

        return redirect()->back()->withErrors($errors)->withInput();
    }
}
