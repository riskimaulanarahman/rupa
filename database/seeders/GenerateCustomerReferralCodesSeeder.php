<?php

namespace Database\Seeders;

use App\Models\Customer;
use Database\Seeders\Concerns\ResolvesDemoTenantOutlet;
use Illuminate\Database\Seeder;

class GenerateCustomerReferralCodesSeeder extends Seeder
{
    use ResolvesDemoTenantOutlet;

    public function run(): void
    {
        if (! app()->has('tenant_id') || ! app()->has('outlet_id')) {
            $this->ensureDemoContextBound();
        }

        $customers = Customer::query()->whereNull('referral_code')->get();

        $count = 0;
        foreach ($customers as $customer) {
            $customer->update([
                'referral_code' => Customer::generateReferralCode(),
            ]);
            $count++;
        }

        $this->command->info("Generated referral codes for {$count} customers.");
    }
}
