<?php

namespace Modules\Habit\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHabitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'frequency' => ['sometimes', 'string', 'in:daily,weekly'],
        ];
    }
}
