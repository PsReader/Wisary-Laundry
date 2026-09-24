<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $pricingType = fake()->randomElement([Service::PRICING_TYPE_KG, Service::PRICING_TYPE_UNIT]);

        return [
            'name' => fake()->unique()->words(3, true),
            'category' => fake()->randomElement(['Cuci Lipat', 'Setrika', 'Cuci Setrika', 'Barang khusus']),
            'pricing_type' => $pricingType,
            'unit' => $pricingType === Service::PRICING_TYPE_KG ? Service::UNIT_KG : Service::UNIT_PCS,
            'price' => fake()->numberBetween(3000, 25000),
            'estimated_days' => fake()->randomElement([0, 1, 3]),
            'is_same_day' => fake()->boolean(20),
            'is_active' => true,
        ];
    }

    public function weightBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'pricing_type' => Service::PRICING_TYPE_KG,
            'unit' => Service::UNIT_KG,
            'is_same_day' => false,
        ]);
    }

    public function unitBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'pricing_type' => Service::PRICING_TYPE_UNIT,
            'unit' => Service::UNIT_PCS,
            'estimated_days' => 3,
            'is_same_day' => false,
        ]);
    }
}
