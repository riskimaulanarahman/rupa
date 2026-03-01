<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'package_id' => ['required', 'exists:packages,id'],
            'price_paid' => ['required', 'numeric', 'min:0'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer harus dipilih.',
            'customer_id.exists' => 'Customer tidak ditemukan.',
            'package_id.required' => 'Paket harus dipilih.',
            'package_id.exists' => 'Paket tidak ditemukan.',
            'price_paid.required' => 'Harga yang dibayar harus diisi.',
            'purchased_at.required' => 'Tanggal pembelian harus diisi.',
        ];
    }
}
