<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Pos\Domain\Enums\BusinessType;
use Modules\Pos\Domain\Enums\PaymentFlowMode;

class StoreOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessTypes = implode(',', array_column(BusinessType::cases(), 'value'));
        $paymentFlows = implode(',', array_column(PaymentFlowMode::cases(), 'value'));

        return [
            'name' => ['required', 'string', 'max:100'],
            'business_type' => ['required', 'string', "in:{$businessTypes}"],
            'payment_flow' => ['sometimes', 'string', "in:{$paymentFlows}"],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
