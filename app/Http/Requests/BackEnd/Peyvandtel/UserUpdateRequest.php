<?php

namespace App\Http\Requests\BackEnd\Peyvandtel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "username" => "sometimes|unique:users,username," . $this->user->id,
            "password" => ["sometimes", "confirmed", Password::min(8)->mixedCase()->numbers()],
            "phone" => "bail|sometimes|numeric|digits:11|starts_with:09|unique:users,phone," . $this->user->id,
            "credit" => "sometimes|numeric|min:" . $this->user->credit,
            "credit_threshold" => "sometimes|numeric|min:1000",
        ];
    }
}
