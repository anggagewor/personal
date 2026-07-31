<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQrOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:150'],
            'items.*.variant_name' => ['nullable', 'string', 'max:100'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
