<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateClinicSettingRequest;
use App\Http\Requests\UpdateOperatingHoursRequest;
use App\Http\Resources\OperatingHourResource;
use App\Models\Setting;
use App\Services\OperatingHoursService;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    public function __construct(private readonly OperatingHoursService $operatingHoursService) {}

    /**
     * Get clinic/business settings
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'clinic' => $this->getClinicInfo(),
                'operating_hours' => $this->getOperatingHours(),
                'appointment' => $this->getAppointmentSettings(),
                'features' => $this->getEnabledFeatures(),
                'business_type' => business_type() ?? 'clinic',
            ],
        ]);
    }

    /**
     * Get clinic info only
     */
    public function clinic(): JsonResponse
    {
        return response()->json([
            'data' => $this->getClinicInfo(),
        ]);
    }

    /**
     * Get operating hours
     */
    public function hours(): JsonResponse
    {
        if (! $this->operatingHoursService->hasCurrentOutletContext()) {
            return $this->missingOutletContextResponse();
        }

        return response()->json([
            'data' => $this->getOperatingHours(),
        ]);
    }

    /**
     * Update clinic info
     */
    public function updateClinic(UpdateClinicSettingRequest $request): JsonResponse
    {
        $fieldMap = [
            'name' => 'clinic_name',
            'phone' => 'clinic_phone',
            'email' => 'clinic_email',
            'address' => 'clinic_address',
            'city' => 'clinic_city',
            'province' => 'clinic_province',
            'postal_code' => 'clinic_postal_code',
            'description' => 'clinic_description',
            'whatsapp' => 'clinic_whatsapp',
            'instagram' => 'clinic_instagram',
            'facebook' => 'clinic_facebook',
            'website' => 'clinic_website',
        ];

        foreach ($fieldMap as $requestField => $settingKey) {
            if ($request->has($requestField)) {
                Setting::setForCurrentContext($settingKey, $request->input($requestField));
            }
        }

        return response()->json([
            'message' => 'Profil klinik berhasil diperbarui.',
            'data' => $this->getClinicInfo(),
        ]);
    }

    /**
     * Update operating hours
     */
    public function updateHours(UpdateOperatingHoursRequest $request): JsonResponse
    {
        if (! $this->operatingHoursService->hasCurrentOutletContext()) {
            return $this->missingOutletContextResponse();
        }

        $hours = $this->operatingHoursService->upsertWeeklyScheduleForCurrentOutlet(
            $request->input('operating_hours', [])
        );

        return response()->json([
            'message' => 'Jam operasional berhasil diperbarui.',
            'data' => OperatingHourResource::collection($hours)->resolve(),
        ]);
    }

    /**
     * Get branding info
     */
    public function branding(): JsonResponse
    {
        $logo = Setting::getForCurrentContext('clinic_logo');

        return response()->json([
            'data' => [
                'logo' => $logo,
                'logo_url' => $logo ? asset('storage/'.$logo) : null,
                'primary_color' => Setting::getForCurrentContext('primary_color', '#f43f5e'),
                'secondary_color' => Setting::getForCurrentContext('secondary_color', '#cc4637'),
            ],
        ]);
    }

    /**
     * Get loyalty program settings
     */
    public function loyalty(): JsonResponse
    {
        return response()->json([
            'data' => [
                'enabled' => in_array('loyalty', config('business.features', [])),
                'points_per_amount' => config('loyalty.points_per_amount', 10000),
                'tiers' => config('loyalty.tiers', [
                    'bronze' => 0,
                    'silver' => 1000,
                    'gold' => 5000,
                    'platinum' => 10000,
                ]),
                'redemption_validity_days' => config('loyalty.redemption_validity_days', 30),
            ],
        ]);
    }

    /**
     * Get referral program settings
     */
    public function referral(): JsonResponse
    {
        return response()->json([
            'data' => [
                'enabled' => config('referral.enabled', true),
                'referrer_points' => config('referral.referrer_bonus_points', 100),
                'referee_points' => config('referral.referee_bonus_points', 50),
                'code_prefix' => config('referral.code_prefix', 'REF'),
            ],
        ]);
    }

    /**
     * Get appointment settings
     */
    public function appointment(): JsonResponse
    {
        return response()->json([
            'data' => $this->getAppointmentSettings(),
        ]);
    }

    /**
     * Get payment methods
     */
    public function paymentMethods(): JsonResponse
    {
        $methods = Setting::get('payment_methods', []);

        // Filter only enabled methods
        $enabledMethods = array_filter($methods, fn($m) => $m['is_enabled'] ?? false);

        return response()->json([
            'data' => array_values($enabledMethods),
        ]);
    }

    /**
     * Get clinic info array
     */
    private function getClinicInfo(): array
    {
        return [
            'name' => Setting::getForCurrentContext('clinic_name', config('app.name')),
            'phone' => Setting::getForCurrentContext('clinic_phone'),
            'email' => Setting::getForCurrentContext('clinic_email'),
            'address' => Setting::getForCurrentContext('clinic_address'),
            'city' => Setting::getForCurrentContext('clinic_city'),
            'province' => Setting::getForCurrentContext('clinic_province'),
            'postal_code' => Setting::getForCurrentContext('clinic_postal_code'),
            'description' => Setting::getForCurrentContext('clinic_description'),
            'whatsapp' => Setting::getForCurrentContext('clinic_whatsapp'),
            'instagram' => Setting::getForCurrentContext('clinic_instagram'),
            'facebook' => Setting::getForCurrentContext('clinic_facebook'),
            'website' => Setting::getForCurrentContext('clinic_website'),
        ];
    }

    /**
     * Get operating hours
     */
    private function getOperatingHours(): array
    {
        if (! $this->operatingHoursService->hasCurrentOutletContext()) {
            return [];
        }

        $hours = $this->operatingHoursService->getWeeklyScheduleForCurrentOutlet();

        return OperatingHourResource::collection($hours)->resolve();
    }

    private function missingOutletContextResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Konteks outlet wajib tersedia untuk jam operasional.',
        ], 400);
    }

    /**
     * Get appointment settings
     */
    private function getAppointmentSettings(): array
    {
        return [
            'slot_duration' => (int) Setting::getForCurrentContext('slot_duration', 30),
            'max_booking_days' => (int) Setting::getForCurrentContext('max_booking_days', 90),
            'min_booking_hours' => (int) Setting::getForCurrentContext('min_booking_hours', 2),
            'allow_walk_in' => (bool) Setting::getForCurrentContext('allow_walk_in', true),
            'require_deposit' => (bool) Setting::getForCurrentContext('require_deposit', false),
            'deposit_amount' => (int) Setting::getForCurrentContext('deposit_amount', 0),
        ];
    }

    /**
     * Get enabled features
     */
    private function getEnabledFeatures(): array
    {
        $allFeatures = [
            'products',
            'treatment_records',
            'packages',
            'customer_packages',
            'loyalty',
            'online_booking',
            'customer_portal',
            'walk_in_queue',
        ];

        $enabledFeatures = business_features();

        $result = [];
        foreach ($allFeatures as $feature) {
            $result[$feature] = (bool) ($enabledFeatures[$feature] ?? false);
        }

        return $result;
    }
}
