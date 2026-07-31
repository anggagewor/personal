<?php

namespace Modules\Pos\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Pos\Domain\Enums\StockAdjustmentType;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $types = implode(',', array_column(StockAdjustmentType::cases(), 'value'));

        return [
            'product_variant_id' => ['required', 'integer'],
            'type' => ['required', 'string', "in:{$types}"],
            'quantity' => ['required', 'integer'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
