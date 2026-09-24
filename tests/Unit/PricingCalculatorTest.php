<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Support\PricingCalculator;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    private function kgService(string $price): Service
    {
        return new Service(['pricing_type' => Service::PRICING_TYPE_KG, 'price' => $price]);
    }

    private function unitService(string $price): Service
    {
        return new Service(['pricing_type' => Service::PRICING_TYPE_UNIT, 'price' => $price]);
    }

    public function test_billed_weight_rounds_up_and_enforces_minimum_four_kilograms(): void
    {
        $calculator = new PricingCalculator;

        $this->assertSame(4, $calculator->billedWeight(2.4));
        $this->assertSame(4, $calculator->billedWeight(2.5));
        $this->assertSame(4, $calculator->billedWeight(3.5));
        $this->assertSame(5, $calculator->billedWeight(4.6));
        $this->assertSame(6, $calculator->billedWeight(5.2));
    }

    public function test_kg_line_subtotal_uses_billed_weight(): void
    {
        $calculator = new PricingCalculator;
        $service = $this->kgService('5000');

        $this->assertSame(20000, $calculator->lineSubtotal($service, 2.5));
        $this->assertSame(20000, $calculator->lineSubtotal($service, 3.5));
        $this->assertSame(25000, $calculator->lineSubtotal($service, 4.6));
    }

    public function test_kg_line_subtotal_is_zero_when_weight_is_empty(): void
    {
        $this->assertSame(0, (new PricingCalculator)->lineSubtotal($this->kgService('5000')));
    }

    public function test_unit_line_subtotal_is_per_quantity_without_minimum(): void
    {
        $calculator = new PricingCalculator;
        $service = $this->unitService('10000');

        $this->assertSame(10000, $calculator->lineSubtotal($service, quantity: 1));
        $this->assertSame(30000, $calculator->lineSubtotal($service, quantity: 3));
        $this->assertSame(0, $calculator->lineSubtotal($service, quantity: 0));
    }

    public function test_total_from_items_sums_all_lines(): void
    {
        $calculator = new PricingCalculator;

        $items = [
            ['service' => $this->kgService('6000'), 'actual_weight' => 3.5],
            ['service' => $this->unitService('7500'), 'quantity' => 2],
            ['service' => $this->kgService('4000'), 'actual_weight' => 4.6],
            ['service' => null, 'actual_weight' => 7],
        ];

        $this->assertSame(24000 + 15000 + 20000, $calculator->totalFromItems($items));
    }

    public function test_total_from_items_is_zero_when_empty(): void
    {
        $this->assertSame(0, (new PricingCalculator)->totalFromItems([]));
    }
}
