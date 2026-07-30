<?php

namespace Modules\Converter\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:50'],
        ];
    }
}
