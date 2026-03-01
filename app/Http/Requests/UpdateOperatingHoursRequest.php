<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOperatingHoursRequest extends FormRequest
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
            'operating_hours' => ['required', 'array', 'min:1'],
            'operating_hours.*.day_of_week' => ['required', 'integer', Rule::in([0, 1, 2, 3, 4, 5, 6])],
            'operating_hours.*.open_time' => ['required_if:operating_hours.*.is_closed,false', 'nullable', 'date_format:H:i'],
            'operating_hours.*.close_time' => ['required_if:operating_hours.*.is_closed,false', 'nullable', 'date_format:H:i', 'after:operating_hours.*.open_time'],
            'operating_hours.*.is_closed' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'operating_hours.required' => 'Data jam operasional wajib diisi.',
            'operating_hours.*.day_of_week.required' => 'Hari wajib diisi.',
            'operating_hours.*.day_of_week.in' => 'Hari harus antara 0 (Minggu) sampai 6 (Sabtu).',
            'operating_hours.*.open_time.required_if' => 'Jam buka wajib diisi jika tidak libur.',
            'operating_hours.*.open_time.date_format' => 'Format jam buka harus HH:mm.',
            'operating_hours.*.close_time.required_if' => 'Jam tutup wajib diisi jika tidak libur.',
            'operating_hours.*.close_time.date_format' => 'Format jam tutup harus HH:mm.',
            'operating_hours.*.close_time.after' => 'Jam tutup harus setelah jam buka.',
        ];
    }
}
