<?php

namespace App\Support;

use App\Models\Service;
use Illuminate\Support\Carbon;

class EstimateCalculator
{
    /**
     * Tanggal estimasi selesai satu layanan, selalu dalam WIB.
     * Layanan Kilat (same-day) dijanjikan selesai hari itu juga.
     * Layanan lainnya menghitung hari kalender dari tanggal terima.
     */
    public function completionDate(Service $service, Carbon $receivedAt): Carbon
    {
        $wib = $receivedAt->copy()->setTimezone('Asia/Jakarta');

        if ($service->is_same_day) {
            return $wib->copy()->startOfDay();
        }

        return $wib->copy()->startOfDay()->addDays(max(0, (int) $service->estimated_days));
    }

    /**
     * Tanggal estimasi seluruh pesanan: tanggal terakhir di antara semua
     * layanan. Null jika belum ada layanan yang dipilih.
     *
     * @param  iterable<Service>  $services
     */
    public function orderCompletionDate(iterable $services, Carbon $receivedAt): ?Carbon
    {
        $latest = null;

        foreach ($services as $service) {
            if (! $service instanceof Service) {
                continue;
            }

            $completion = $this->completionDate($service, $receivedAt);

            if ($latest === null || $completion->greaterThan($latest)) {
                $latest = $completion;
            }
        }

        return $latest;
    }
}
