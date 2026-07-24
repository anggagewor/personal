<?php

namespace Modules\Quote\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
            'author' => ['nullable', 'string', 'max:255'],
        ];
    }
}
