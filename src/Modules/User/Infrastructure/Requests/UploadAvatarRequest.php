<?php

namespace Modules\User\Infrastructure\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'File avatar wajib dipilih.',
            'avatar.image' => 'File harus berupa gambar.',
            'avatar.mimes' => 'Format yang didukung: JPG, PNG, WebP.',
            'avatar.max' => 'Ukuran maksimal 2MB.',
        ];
    }
}
