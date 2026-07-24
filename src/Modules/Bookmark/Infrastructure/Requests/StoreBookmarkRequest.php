<?php

namespace Modules\Bookmark\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookmarkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
