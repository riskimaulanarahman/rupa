<?php

namespace App\Support\Customers;

use Illuminate\Validation\Rule;

class CustomerProfileRules
{
    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\In>>
     */
    public static function rules(?string $businessType = null): array
    {
        $typeOptions = business_profile_option_keys('type', $businessType);
        $concernOptions = business_profile_option_keys('concerns', $businessType);

        $typeRules = [
            business_profile_field_required('type', $businessType) ? 'required' : 'nullable',
            'string',
        ];

        if ($typeOptions !== []) {
            $typeRules[] = Rule::in($typeOptions);
        }

        $concernItemRules = ['string'];

        if ($concernOptions !== []) {
            $concernItemRules[] = Rule::in($concernOptions);
        }

        return [
            'skin_type' => $typeRules,
            'skin_concerns' => ['nullable', 'array'],
            'skin_concerns.*' => $concernItemRules,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(?string $businessType = null): array
    {
        return [
            'skin_type' => business_profile_field_label('type', $businessType),
            'skin_concerns' => business_profile_field_label('concerns', $businessType),
        ];
    }
}
