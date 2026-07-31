<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'category_id' => ['required', 'integer'],
            'sku' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
