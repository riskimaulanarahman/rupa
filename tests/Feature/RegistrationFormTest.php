<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_renders_subdomain_suffix_without_overlay_layout(): void
    {
        config(['app.url' => 'https://rupa.test']);

        $plan = $this->createPlan();

        $this->get('http://localhost/register/plan/'.$plan->slug)
            ->assertOk()
            ->assertSee('.rupa.test')
            ->assertSee('flex flex-col sm:flex-row', false)
            ->assertDontSee('absolute right-3', false);
    }

    public function test_registration_form_renders_ip_host_suffix_for_subdomain_field(): void
    {
        config(['app.url' => 'http://172.20.10.2']);

        $plan = $this->createPlan();

        $this->get('http://localhost/register/plan/'.$plan->slug)
            ->assertOk()
            ->assertSee('.172.20.10.2');
    }

    private function createPlan(): Plan
    {
        $suffix = Str::lower(Str::random(8));

        return Plan::query()->create([
            'name' => 'Plan '.$suffix,
            'slug' => 'plan-'.$suffix,
            'description' => 'Paket uji registrasi',
            'features' => ['Manajemen outlet'],
            'price_monthly' => 100000,
            'price_yearly' => 1000000,
            'max_outlets' => 5,
            'trial_days' => 14,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
        ]);
    }
}
