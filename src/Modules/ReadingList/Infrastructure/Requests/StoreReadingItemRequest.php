<?php

namespace Modules\ReadingList\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:2048'],
        ];
    }
}
