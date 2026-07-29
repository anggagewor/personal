<?php

namespace Modules\Vault\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVaultEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'encrypted_password' => ['required', 'string'],
            'url' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:100'],
        ];
    }
}
