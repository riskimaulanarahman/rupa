<?php

namespace App\Http\Requests;

use App\Support\Customers\CustomerProfileRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
        $customerId = $this->route('customer')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^08[0-9]{8,13}$/',
                Rule::unique('customers', 'phone')->ignore($customerId),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'address' => ['nullable', 'string', 'max:500'],
            'allergies' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'referral_code' => ['nullable', 'string', 'max:20'],
        ] + CustomerProfileRules::rules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor telepon harus dimulai dengan 08 dan terdiri dari 10-15 digit.',
            'phone.unique' => 'Nomor telepon sudah terdaftar.',
            'birthdate.before' => 'Tanggal lahir harus sebelum hari ini.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return CustomerProfileRules::attributes();
    }
}
