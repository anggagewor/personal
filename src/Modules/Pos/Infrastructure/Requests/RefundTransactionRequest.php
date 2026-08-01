<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefundTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.transaction_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:500'],
            'refund_method' => ['nullable', 'string', 'in:cash,original_method,store_credit'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Minimal 1 item harus dipilih untuk refund.',
            'items.min' => 'Minimal 1 item harus dipilih untuk refund.',
            'reason.required' => 'Alasan refund wajib diisi.',
        ];
    }
}
