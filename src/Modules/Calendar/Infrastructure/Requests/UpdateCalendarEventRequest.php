<?php

namespace Modules\Calendar\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_at' => ['sometimes', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'color' => ['nullable', 'string', 'max:20'],
            'all_day' => ['sometimes', 'boolean'],
        ];
    }
}
