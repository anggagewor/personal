<?php

namespace Modules\Goal\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Goal\Domain\Enums\GoalStatus;

class UpdateGoalRequest extends FormRequest
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
            'target_date' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::enum(GoalStatus::class)],
        ];
    }
}
