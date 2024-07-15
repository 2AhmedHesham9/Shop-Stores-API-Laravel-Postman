<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterAdminRequest extends FormRequest
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

            'name' => 'required',
            'email' => 'required|unique:websiteadmin',
            'password' => 'required',
            "role" => "required",
            'phonenumber' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'role.required' => 'The role field is required.',
            'phonenumber.required' => 'The phone number field is required.',
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
