<?php

namespace App\Support;

use App\Models\Order;

class StatusFlow
{
    /**
     * Urutan status pengerjaan. Maju bebas: staf boleh melompati tahap,
     * namun tidak boleh mundur.
     */
    public const ORDERED = [
        Order::STATUS_DITERIMA,
        Order::STATUS_SEDANG_DICUCI,
        Order::STATUS_SEDANG_DIKERINGKAN,
        Order::STATUS_SEDANG_DISETRIKA,
        Order::STATUS_SEDANG_DILIPAT,
        Order::STATUS_SIAP_DIAMBIL,
    ];

    /**
     * Cek apakah perpindahan status diperbolehkan. Pembatalan (Dibatalkan)
     * bukan bagian dari alur ini — ditangani di Milestone 13.
     */
    public static function canTransition(string $from, string $to): bool
    {
        $fromIndex = array_search($from, self::ORDERED, true);
        $toIndex = array_search($to, self::ORDERED, true);

        return $fromIndex !== false && $toIndex !== false && $toIndex > $fromIndex;
    }

    /**
     * Status yang dapat dicapai ke depan dari status sekarang.
     *
     * @return array<int, string>
     */
    public static function nextStatuses(string $status): array
    {
        $fromIndex = array_search($status, self::ORDERED, true);

        if ($fromIndex === false) {
            return [];
        }

        return array_slice(self::ORDERED, $fromIndex + 1);
    }
}
