<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'pricing_type', 'unit', 'price', 'estimated_days', 'is_same_day', 'is_active'])]
class Service extends Model
{
    public const PRICING_TYPE_KG = 'kg';

    public const PRICING_TYPE_UNIT = 'unit';

    public const UNIT_KG = 'kg';

    public const UNIT_PCS = 'Unit';

    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'estimated_days' => 'integer',
            'is_same_day' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function pricingTypeLabel(string $pricingType): string
    {
        return match ($pricingType) {
            self::PRICING_TYPE_KG => 'Kilogram',
            self::PRICING_TYPE_UNIT => 'Per Unit',
            default => $pricingType,
        };
    }

    /**
     * Label estimasi selesai, mis. "Hari yang sama" atau "3 hari kalender".
     */
    public static function estimateLabel(int $days): string
    {
        return $days === 0 ? 'Hari yang sama' : "{$days} hari kalender";
    }

    /**
     * Hanya layanan yang bisa dipilih pada pesanan baru.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Keterangan harga untuk form pesanan, mis. "Rp5.000/kg".
     */
    protected function priceLabel(): Attribute
    {
        return Attribute::get(fn () => format_rupiah((float) $this->price).'/'.($this->pricing_type === self::PRICING_TYPE_KG ? 'kg' : 'unit'));
    }
}
