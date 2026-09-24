<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'handover_action', 'recorded_by', 'occurred_at', 'note', 'failure_reason'])]
class OrderHandoverHistory extends Model
{
    public const UPDATED_AT = null;

    public const ACTION_SUDAH_DIAMBIL = 'sudah_diambil';

    public const ACTION_SUDAH_DIANTAR = 'sudah_diantar';

    public const ACTION_GAGAL = 'gagal';

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function actionLabel(string $action): string
    {
        return match ($action) {
            self::ACTION_SUDAH_DIAMBIL => 'Diambil',
            self::ACTION_SUDAH_DIANTAR => 'Diantar',
            self::ACTION_GAGAL => 'Gagal',
            default => $action,
        };
    }
}
