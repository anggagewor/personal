<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseOpenBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'string', 'max:50'],
            'payment_method_type' => ['required', 'string', 'in:cash,bank_transfer,e_wallet,custom'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
