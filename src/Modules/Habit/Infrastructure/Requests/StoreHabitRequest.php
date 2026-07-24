<?php

namespace Modules\Habit\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHabitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'frequency' => ['sometimes', 'string', 'in:daily,weekly'],
        ];
    }
}
