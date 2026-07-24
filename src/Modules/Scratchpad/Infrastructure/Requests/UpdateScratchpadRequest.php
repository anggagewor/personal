<?php

namespace Modules\Scratchpad\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScratchpadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['sometimes', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'position' => ['sometimes', 'integer'],
        ];
    }
}
