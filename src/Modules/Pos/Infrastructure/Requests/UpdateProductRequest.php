<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'base_price' => ['sometimes', 'numeric', 'min:0'],
            'category_id' => ['sometimes', 'integer'],
            'sku' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'string', 'max:255'],
            'has_variants' => ['sometimes', 'boolean'],
            'track_stock' => ['sometimes', 'boolean'],
            'variants' => ['sometimes', 'array'],
            'variants.*.name' => ['required_with:variants', 'string', 'max:100'],
            'variants.*.sku' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['required_with:variants', 'numeric', 'min:0'],
            'variants.*.stock_quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
