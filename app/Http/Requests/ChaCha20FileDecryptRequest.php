<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChaCha20FileDecryptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'    => ['required', 'file', 'max:5120'], // max 5 MB

            // Key & Nonce: WAJIB untuk dekripsi (harus sama dengan saat enkripsi)
            'key'     => ['required', 'string', 'regex:/^[0-9a-fA-F]{64}$/'],
            'nonce'   => ['required', 'string', 'regex:/^[0-9a-fA-F]{24}$/'],

            'counter' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'  => 'File tidak boleh kosong.',
            'file.file'      => 'Upload harus berupa file yang valid.',
            'file.max'       => 'Ukuran file maksimal 5 MB.',
            'key.required'   => 'Secret Key wajib diisi untuk dekripsi.',
            'key.regex'      => 'Key harus tepat 64 karakter hexadecimal (256-bit).',
            'nonce.required' => 'Nonce wajib diisi untuk dekripsi.',
            'nonce.regex'    => 'Nonce harus tepat 24 karakter hexadecimal (96-bit).',
            'counter.integer' => 'Counter harus berupa bilangan bulat.',
            'counter.max'    => 'Counter maksimal 2^32 - 1.',
        ];
    }
}
