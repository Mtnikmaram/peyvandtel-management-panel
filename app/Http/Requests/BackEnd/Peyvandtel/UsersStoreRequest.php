<?php

namespace App\Http\Requests\BackEnd\Peyvandtel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UsersStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "username" => "required|unique:users,username",
            "password" => ["sometimes","confirmed", Password::min(8)->mixedCase()->numbers()],
            "phone" => "bail|required|numeric|digits:11|starts_with:09|unique:users,phone",
            "name" => "required",
            "credit_threshold" => "required|numeric|min:1000",
        ];
    }
}
