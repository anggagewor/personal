<?php

namespace Modules\Goal\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Goal\Domain\Enums\GoalStatus;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::enum(GoalStatus::class)],
            'milestones' => ['sometimes', 'array'],
            'milestones.*.title' => ['required_with:milestones', 'string', 'max:255'],
        ];
    }
}
