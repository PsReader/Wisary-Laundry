<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $seq = fake()->unique()->numberBetween(1, 99999);

        return [
            'order_number' => $seq.now()->format('dmY'),
            'customer_id' => Customer::factory(),
            'received_at' => now(),
            'status' => Order::STATUS_DITERIMA,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
            'payment_status' => Order::PAYMENT_BELUM_LUNAS,
            'method' => fake()->randomElement([Order::METHOD_DIAMBIL, Order::METHOD_DIANTAR]),
            'delivery_address' => null,
            'estimated_completion_at' => now()->addDays(3),
            'total_price' => 0,
            'paid_amount' => 0,
            'payment_method' => null,
            'notes' => fake()->optional()->sentence(5),
            'cancellation_reason' => null,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'created_by' => User::factory(),
        ];
    }

    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_DITERIMA,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_SIAP_DIAMBIL,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
        ]);
    }

    public function pickup(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => Order::METHOD_DIAMBIL,
            'delivery_address' => null,
        ]);
    }

    public function delivery(?string $address = 'Jl. Contoh No. 1, Jakarta'): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => Order::METHOD_DIANTAR,
            'delivery_address' => $address,
        ]);
    }

    public function handedOver(string $action): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_SIAP_DIAMBIL,
            'handover_status' => $action,
        ]);
    }

    public function cancelled(string $reason = 'Pembatalan oleh pelanggan'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_DIBATALKAN,
            'handover_status' => Order::HANDOVER_BELUM_DISERAHKAN,
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $attributes['total_price'],
            'payment_status' => Order::PAYMENT_LUNAS,
            'payment_method' => 'tunai',
        ]);
    }

    public function partial(int $paidAmount = 0): static
    {
        return $this->state(fn (array $attributes) => [
            'paid_amount' => $paidAmount,
            'payment_status' => $paidAmount > 0 ? Order::PAYMENT_LUNAS_SEBAGIAN : Order::PAYMENT_BELUM_LUNAS,
            'payment_method' => $paidAmount > 0 ? 'tunai' : null,
        ]);
    }
}
