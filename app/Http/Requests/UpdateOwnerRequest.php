<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOwnerRequest extends FormRequest
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
            "nameOfOwner" => "required",
            //this for email validation because the email is unique
            //so if you update another value as password it will return
            //that email already taken so i use this validation
            "email" => ['required','email', Rule::unique('users')->ignore($this->user()->id)],
            "password" => "required",
            "PhoneNumberOfOwner" => "required",
        ];
    }
}
