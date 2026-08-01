<?php

namespace Modules\AuditLog\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\AuditLog\Domain\Enums\AuditEvent;

class QueryAuditLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event' => ['sometimes', 'string', Rule::enum(AuditEvent::class)],
            'auditable_type' => ['sometimes', 'string', 'max:255'],
            'auditable_id' => ['sometimes', 'integer'],
            'tags' => ['sometimes', 'string', 'max:255'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
