<?php

namespace App\Http\Requests\BackEnd\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class ServiceStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "serviceId" => [
                "required",
                Rule::exists('services', 'id')
                    ->whereNotNull('credentials')
                    ->where('active', 1)
            ],
            "attachments" => "nullable|array",
            "attachments.*" => [
                "required",
                File::defaults()->max(50 * 1024),
            ]
        ];
    }
}
