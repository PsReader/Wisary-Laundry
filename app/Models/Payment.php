<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'user_id', 'amount', 'payment_method', 'received_at', 'note'])]
class Payment extends Model
{
    public const UPDATED_AT = null;

    public const METHOD_TUNAI = 'tunai';

    public const METHOD_TRANSFER = 'transfer';

    public const METHOD_KARTU = 'kartu';

    public const METHOD_EWALLET = 'ewallet';

    public const METHOD_LAINNYA = 'lainnya';

    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'received_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<int, string>
     */
    public static function methods(): array
    {
        return [
            self::METHOD_TUNAI,
            self::METHOD_TRANSFER,
            self::METHOD_KARTU,
            self::METHOD_EWALLET,
            self::METHOD_LAINNYA,
        ];
    }

    public static function methodLabel(string $method): string
    {
        return match ($method) {
            self::METHOD_TUNAI => 'Tunai',
            self::METHOD_TRANSFER => 'Transfer Bank',
            self::METHOD_KARTU => 'Kartu',
            self::METHOD_EWALLET => 'E-Wallet',
            self::METHOD_LAINNYA => 'Lainnya',
            default => $method,
        };
    }
}
