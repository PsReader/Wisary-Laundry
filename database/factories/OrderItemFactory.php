<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomElement([3000, 4000, 5000, 6000, 8000, 10000, 12000]);
        $actual = fake()->randomFloat(1, 2.0, 10.0);
        $billed = max(4, (float) ceil($actual));

        return [
            'order_id' => Order::factory(),
            'service_id' => Service::factory()->weightBased(),
            'service_name' => fake()->unique()->words(3, true),
            'pricing_type' => Service::PRICING_TYPE_KG,
            'unit' => Service::UNIT_KG,
            'price' => $price,
            'actual_weight' => $actual,
            'billed_weight' => $billed,
            'quantity' => 1,
            'subtotal' => $billed * $price,
            'note' => null,
        ];
    }

    public function unitBased(int $quantity = 1, ?int $price = null): static
    {
        $price ??= fake()->randomElement([12000, 15000, 20000, 25000]);

        return $this->state(fn (array $attributes) => [
            'service_id' => Service::factory()->unitBased(),
            'pricing_type' => Service::PRICING_TYPE_UNIT,
            'unit' => Service::UNIT_PCS,
            'price' => $price,
            'actual_weight' => null,
            'billed_weight' => null,
            'quantity' => $quantity,
            'subtotal' => $quantity * $price,
        ]);
    }
}
