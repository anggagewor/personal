<?php

namespace Modules\Journal\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Journal\Domain\Enums\JournalMood;

class StoreJournalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'mood' => ['nullable', Rule::enum(JournalMood::class)],
            'date' => ['nullable', 'date'],
        ];
    }
}
