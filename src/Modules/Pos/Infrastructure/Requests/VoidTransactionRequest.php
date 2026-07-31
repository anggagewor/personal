<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
