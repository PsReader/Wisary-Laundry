<?php

namespace App\Support;

use App\Models\Service;

class PricingCalculator
{
    public const MIN_BILLED_KG = 4;

    /**
     * Berat yang ditagihkan: pembulatan ke atas, dengan minimum empat kilogram.
     */
    public function billedWeight(float $actualWeight): int
    {
        return max(self::MIN_BILLED_KG, (int) ceil($actualWeight));
    }

    /**
     * Subtotal satu baris layanan. Kilogram dihitung dari berat yang
     * ditagihkan; unit dihitung per jumlah unit tanpa minimum kilogram.
     */
    public function lineSubtotal(Service $service, float $actualWeight = 0, int $quantity = 0): int
    {
        $price = (int) round((float) $service->price);

        if ($service->pricing_type === Service::PRICING_TYPE_KG) {
            return $actualWeight > 0 ? $price * $this->billedWeight($actualWeight) : 0;
        }

        return $price * max(0, $quantity);
    }

    /**
     * Total pesanan dari daftar baris layanan.
     *
     * @param  iterable<array{service: ?Service, actual_weight?: float, quantity?: int}>  $items
     */
    public function totalFromItems(iterable $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $service = $item['service'] ?? null;

            if (! $service instanceof Service) {
                continue;
            }

            $total += $this->lineSubtotal(
                $service,
                $item['actual_weight'] ?? 0,
                $item['quantity'] ?? 0,
            );
        }

        return $total;
    }
}
