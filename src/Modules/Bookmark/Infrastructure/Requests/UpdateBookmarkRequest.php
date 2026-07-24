<?php

namespace Modules\Bookmark\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'url' => ['sometimes', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
