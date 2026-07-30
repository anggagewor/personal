<?php

namespace Modules\Converter\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'required', 'integer'],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'symbol' => ['sometimes', 'required', 'string', 'max:20'],
            'to_base' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'is_base' => ['sometimes', 'boolean'],
        ];
    }
}
