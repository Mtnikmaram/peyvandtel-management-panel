<?php

namespace App\Http\Requests\BackEnd\Peyvandtel;

use Illuminate\Foundation\Http\FormRequest;

class ServicesSetUsernamePasswordCredentialRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "username" => "required",
            "password" => "required"
        ];
    }
}
