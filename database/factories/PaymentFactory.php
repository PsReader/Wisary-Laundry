<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => User::factory(),
            'amount' => fake()->numberBetween(20000, 200000),
            'payment_method' => fake()->randomElement([
                Payment::METHOD_TUNAI,
                Payment::METHOD_TRANSFER,
                Payment::METHOD_KARTU,
                Payment::METHOD_EWALLET,
                Payment::METHOD_LAINNYA,
            ]),
            'received_at' => now(),
            'note' => fake()->optional()->sentence(5),
        ];
    }
}
