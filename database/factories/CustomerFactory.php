<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'whatsapp' => '08'.fake()->numerify('##########'),
            'address' => fake()->optional()->sentence(6),
            'notes' => fake()->optional()->sentence(8),
        ];
    }
}
