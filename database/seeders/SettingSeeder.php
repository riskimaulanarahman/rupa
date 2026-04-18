<?php

namespace Database\Seeders;

use App\Models\Setting;
use Database\Seeders\Concerns\ResolvesDemoTenantOutlet;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    use ResolvesDemoTenantOutlet;

    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'clinic_name', 'value' => 'Rupa Clinic', 'type' => 'string'],
            ['key' => 'clinic_address', 'value' => 'Jl. Palagan Tentara Pelajar No.27, Jongkang, Sariharjo, Kec. Ngaglik, Kabupaten Sleman, Daerah Istimewa Yogyakarta 55581', 'type' => 'string'],
            ['key' => 'clinic_phone', 'value' => '6285640899224', 'type' => 'string'],
            ['key' => 'clinic_email', 'value' => 'hello@jagoflutter.com', 'type' => 'string'],
            ['key' => 'clinic_logo', 'value' => null, 'type' => 'string'],

            // Transaction
            ['key' => 'tax_percentage', 'value' => '0', 'type' => 'integer'],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'type' => 'string'],
            ['key' => 'currency', 'value' => 'IDR', 'type' => 'string'],

            // Appointment
            ['key' => 'slot_duration', 'value' => '30', 'type' => 'integer'],
            ['key' => 'allow_walk_in', 'value' => '1', 'type' => 'boolean'],

            // System
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'type' => 'string'],
        ];

        [$tenant, $outlet] = $this->ensureDemoContextBound();

        foreach ($settings as $setting) {
            Setting::withoutGlobalScopes()->updateOrCreate(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'tenant_id' => $tenant->id,
                    'outlet_id' => $outlet->id,
                ])
            );
        }
    }
}
