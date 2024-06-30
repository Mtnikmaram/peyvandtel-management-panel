<?php

namespace App\Http\Requests\BackEnd\Peyvandtel;

use Illuminate\Foundation\Http\FormRequest;

class ServicePricesStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "serviceId" => "required|exists:services,id|unique:service_prices,service_id",
            "amount" => "required|numeric|min:0",
            "setting" => "nullable|array",
            "setting.*.key" => "required",
            "setting.*.value" => "required"
        ];
    }
}
