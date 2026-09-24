<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_id' => null,
            'amount' => fake()->numberBetween(5000, 100000),
            'reason' => fake()->sentence(6),
            'method' => fake()->randomElement(Payment::methods()),
            'processed_by' => User::factory(),
            'refunded_at' => now(),
        ];
    }
}
