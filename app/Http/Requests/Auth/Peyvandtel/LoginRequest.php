<?php

namespace App\Http\Requests\Auth\Peyvandtel;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "username" => "required|string",
            "password" => "required",
            "model" => "required"
        ];
    }
}
