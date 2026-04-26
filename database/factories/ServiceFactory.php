<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => ServiceCategory::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'pricing_mode' => Service::PRICING_MODE_FIXED,
            'price' => fake()->numberBetween(50000, 500000),
            'price_min' => null,
            'price_max' => null,
            'incentive' => fake()->numberBetween(10000, 100000),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90, 120]),
            'is_active' => true,
        ];
    }
}
