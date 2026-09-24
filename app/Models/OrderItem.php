<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id', 'service_id', 'service_name', 'pricing_type', 'unit', 'price',
    'actual_weight', 'billed_weight', 'quantity', 'subtotal', 'note',
])]
class OrderItem extends Model
{
    public const UPDATED_AT = null;

    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'actual_weight' => 'decimal:2',
            'billed_weight' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function isWeightBased(): bool
    {
        return $this->pricing_type === Service::PRICING_TYPE_KG;
    }
}
