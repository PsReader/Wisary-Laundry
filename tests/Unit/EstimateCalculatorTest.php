<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Support\EstimateCalculator;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class EstimateCalculatorTest extends TestCase
{
    private function service(int $days, bool $sameDay = false): Service
    {
        return new Service(['estimated_days' => $days, 'is_same_day' => $sameDay]);
    }

    private function wib(string $datetime): Carbon
    {
        return Carbon::parse($datetime, 'Asia/Jakarta');
    }

    public function test_reguler_adds_three_calendar_days_including_weekend(): void
    {
        $calculator = new EstimateCalculator;
        // Jumat
        $received = $this->wib('2026-09-11 10:00');

        $this->assertTrue(
            $calculator->completionDate($this->service(3), $received)->equalTo($this->wib('2026-09-14 00:00'))
        );
    }

    public function test_express_adds_one_calendar_day(): void
    {
        $calculator = new EstimateCalculator;

        $this->assertTrue(
            $calculator->completionDate($this->service(1), $this->wib('2026-09-11 10:00'))
                ->equalTo($this->wib('2026-09-12 00:00'))
        );
    }

    public function test_same_day_service_always_completes_today(): void
    {
        $calculator = new EstimateCalculator;

        $this->assertTrue(
            $calculator->completionDate($this->service(0, true), $this->wib('2026-09-11 09:00'))
                ->equalTo($this->wib('2026-09-11 00:00'))
        );
        $this->assertTrue(
            $calculator->completionDate($this->service(0, true), $this->wib('2026-09-11 23:59'))
                ->equalTo($this->wib('2026-09-11 00:00'))
        );
    }

    public function test_order_takes_latest_of_mixed_services(): void
    {
        $calculator = new EstimateCalculator;
        $received = $this->wib('2026-09-11 10:00');

        $completion = $calculator->orderCompletionDate([
            $this->service(0, true),
            $this->service(3),
        ], $received);

        $this->assertTrue($completion->equalTo($this->wib('2026-09-14 00:00')));
    }

    public function test_order_returns_null_when_no_services(): void
    {
        $this->assertNull((new EstimateCalculator)->orderCompletionDate([], $this->wib('2026-09-11 10:00')));
    }

    public function test_completion_normalizes_non_wib_timezone(): void
    {
        $calculator = new EstimateCalculator;
        $received = Carbon::parse('2026-09-11 10:00', 'UTC');

        $completion = $calculator->completionDate($this->service(1), $received);

        $this->assertTrue($completion->equalTo($this->wib('2026-09-12 00:00')));
    }
}
