<?php

namespace Modules\Supplier\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer'],
            'default_unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
