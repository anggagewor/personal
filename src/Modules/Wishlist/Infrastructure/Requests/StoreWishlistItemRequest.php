<?php

namespace Modules\Wishlist\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWishlistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
