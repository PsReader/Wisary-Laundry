<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Support\StatusFlow;
use PHPUnit\Framework\TestCase;

class StatusFlowTest extends TestCase
{
    public function test_forward_transition_is_allowed(): void
    {
        $this->assertTrue(StatusFlow::canTransition(Order::STATUS_DITERIMA, Order::STATUS_SEDANG_DICUCI));
        $this->assertTrue(StatusFlow::canTransition(Order::STATUS_SEDANG_DIKERINGKAN, Order::STATUS_SEDANG_DISETRIKA));
        $this->assertTrue(StatusFlow::canTransition(Order::STATUS_SEDANG_DILIPAT, Order::STATUS_SIAP_DIAMBIL));
    }

    public function test_forward_skip_transition_is_allowed(): void
    {
        $this->assertTrue(StatusFlow::canTransition(Order::STATUS_DITERIMA, Order::STATUS_SEDANG_DISETRIKA));
        $this->assertTrue(StatusFlow::canTransition(Order::STATUS_SEDANG_DICUCI, Order::STATUS_SIAP_DIAMBIL));
        $this->assertTrue(StatusFlow::canTransition(Order::STATUS_DITERIMA, Order::STATUS_SIAP_DIAMBIL));
    }

    public function test_backward_transition_is_rejected(): void
    {
        $this->assertFalse(StatusFlow::canTransition(Order::STATUS_SEDANG_DICUCI, Order::STATUS_DITERIMA));
        $this->assertFalse(StatusFlow::canTransition(Order::STATUS_SIAP_DIAMBIL, Order::STATUS_SEDANG_DILIPAT));
    }

    public function test_same_status_is_rejected(): void
    {
        $this->assertFalse(StatusFlow::canTransition(Order::STATUS_DITERIMA, Order::STATUS_DITERIMA));
    }

    public function test_cancellation_is_not_part_of_the_flow(): void
    {
        $this->assertFalse(StatusFlow::canTransition(Order::STATUS_DITERIMA, Order::STATUS_DIBATALKAN));
        $this->assertFalse(StatusFlow::canTransition(Order::STATUS_SIAP_DIAMBIL, Order::STATUS_DIBATALKAN));
    }

    public function test_next_statuses_are_forward_only(): void
    {
        $this->assertSame([
            Order::STATUS_SEDANG_DICUCI,
            Order::STATUS_SEDANG_DIKERINGKAN,
            Order::STATUS_SEDANG_DISETRIKA,
            Order::STATUS_SEDANG_DILIPAT,
            Order::STATUS_SIAP_DIAMBIL,
        ], StatusFlow::nextStatuses(Order::STATUS_DITERIMA));

        $this->assertSame([], StatusFlow::nextStatuses(Order::STATUS_SIAP_DIAMBIL));
        $this->assertSame([], StatusFlow::nextStatuses(Order::STATUS_DIBATALKAN));
    }
}
