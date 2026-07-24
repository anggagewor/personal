<?php

namespace Modules\Pomodoro\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePomodoroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'task_id' => ['nullable', 'integer'],
            'duration' => ['sometimes', 'integer', 'min:1', 'max:120'],
        ];
    }
}
