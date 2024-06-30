<?php

namespace App\Http\Requests\BackEnd\Peyvandtel;

use Illuminate\Foundation\Http\FormRequest;

class ServicePriceUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "amount" => "required|numeric|min:0",
            "setting" => "nullable|array",
            "setting.*.key" => "required",
            "setting.*.value" => "required"
        ];
    }
}
