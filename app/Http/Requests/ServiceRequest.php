<?php

namespace App\Http\Requests;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<string>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:service_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'pricing_mode' => ['required', 'in:'.Service::PRICING_MODE_FIXED.','.Service::PRICING_MODE_RANGE],
            'price' => ['nullable', 'numeric', 'min:0', 'required_if:pricing_mode,'.Service::PRICING_MODE_FIXED],
            'price_min' => ['nullable', 'numeric', 'min:0', 'required_if:pricing_mode,'.Service::PRICING_MODE_RANGE],
            'price_max' => ['nullable', 'numeric', 'min:0', 'required_if:pricing_mode,'.Service::PRICING_MODE_RANGE],
            'incentive' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama layanan wajib diisi.',
            'duration_minutes.required' => 'Durasi wajib diisi.',
            'duration_minutes.min' => 'Durasi minimal 5 menit.',
            'duration_minutes.max' => 'Durasi maksimal 480 menit (8 jam).',
            'price.required_if' => 'Harga wajib diisi untuk mode harga pasti.',
            'pricing_mode.required' => 'Mode harga wajib dipilih.',
            'pricing_mode.in' => 'Mode harga tidak valid.',
            'price.min' => 'Harga tidak boleh negatif.',
            'price_min.required_if' => 'Harga minimum wajib diisi untuk mode rentang.',
            'price_min.min' => 'Harga minimum tidak boleh negatif.',
            'price_max.required_if' => 'Harga maksimum wajib diisi untuk mode rentang.',
            'price_max.min' => 'Harga maksimum tidak boleh negatif.',
            'incentive.min' => 'Insentif tidak boleh negatif.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('pricing_mode') !== Service::PRICING_MODE_RANGE) {
                return;
            }

            $priceMin = $this->input('price_min');
            $priceMax = $this->input('price_max');

            if ($priceMin === null || $priceMax === null || $priceMin === '' || $priceMax === '') {
                return;
            }

            if ((float) $priceMax < (float) $priceMin) {
                $validator->errors()->add('price_max', 'Harga maksimum harus lebih besar atau sama dengan harga minimum.');
            }
        });
    }
}
