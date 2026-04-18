<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $starterPlan = Plan::query()->where('slug', 'starter')->firstOrFail();
        $proPlan = Plan::query()->where('slug', 'pro')->firstOrFail();

        // Tenant PRO (2 outlet)
        Tenant::updateOrCreate(
            ['slug' => 'rupa-demo'],
            [
                'name' => 'Rupa Pro Tenant',
                'slug' => 'rupa-demo',
                'plan_id' => $proPlan->id,
                'owner_name' => 'Owner Pro',
                'owner_email' => 'owner.pro@rupa.test',
                'status' => 'active',
                'trial_ends_at' => null,
                'subscription_ends_at' => now()->addMonth(),
                'is_read_only' => false,
            ]
        );

        // Tenant STARTER (1 outlet)
        Tenant::updateOrCreate(
            ['slug' => 'rupa-starter'],
            [
                'name' => 'Rupa Starter Tenant',
                'slug' => 'rupa-starter',
                'plan_id' => $starterPlan->id,
                'owner_name' => 'Owner Starter',
                'owner_email' => 'owner.starter@rupa.test',
                'status' => 'active',
                'trial_ends_at' => now()->addDays(30),
                'subscription_ends_at' => now()->addMonth(),
                'is_read_only' => false,
            ]
        );
    }
}
