<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', 'in:service,package,product,other'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.package_id' => ['nullable', 'exists:packages,id'],
            'items.*.customer_package_id' => ['nullable', 'exists:customer_packages,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', 'string', 'max:50'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
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
            'items.required' => 'Minimal 1 item harus ditambahkan.',
            'items.min' => 'Minimal 1 item harus ditambahkan.',
            'items.*.item_name.required' => 'Nama item harus diisi.',
            'items.*.quantity.required' => 'Jumlah harus diisi.',
            'items.*.unit_price.required' => 'Harga harus diisi.',
        ];
    }
}
