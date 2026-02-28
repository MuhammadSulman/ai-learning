<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => 'required|string|max:200',
            'context' => 'required|string|max:5000',
            'temperature' => 'sometimes|numeric|between:0,2',
            'max_tokens' => 'sometimes|integer|between:50,4096',
            'stream' => 'sometimes|boolean',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);

        return array_merge([
            'temperature' => 0.7,
            'max_tokens' => 1024,
            'stream' => false,
        ], $data);
    }
}
