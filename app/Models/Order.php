<?php

namespace App\Models;

use App\Support\Audit;
use App\Support\StatusFlow;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

#[Fillable([
    'order_number', 'customer_id', 'received_at', 'status', 'handover_status',
    'payment_status', 'method', 'delivery_address', 'estimated_completion_at',
    'total_price', 'paid_amount', 'payment_method', 'notes', 'cancellation_reason',
    'cancelled_at', 'cancelled_by', 'created_by',
])]
class Order extends Model
{
    // Status pengerjaan
    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_SEDANG_DICUCI = 'sedang_dicuci';

    public const STATUS_SEDANG_DIKERINGKAN = 'sedang_dikeringkan';

    public const STATUS_SEDANG_DISETRIKA = 'sedang_disetrika';

    public const STATUS_SEDANG_DILIPAT = 'sedang_dilipat';

    public const STATUS_SIAP_DIAMBIL = 'siap_diambil';

    public const STATUS_DIBATALKAN = 'dibatalkan';

    // Status serah terima
    public const HANDOVER_BELUM_DISERAHKAN = 'belum_diserahkan';

    public const HANDOVER_SUDAH_DIAMBIL = 'sudah_diambil';

    public const HANDOVER_SUDAH_DIANTAR = 'sudah_diantar';

    // Status pembayaran
    public const PAYMENT_BELUM_LUNAS = 'belum_lunas';

    public const PAYMENT_LUNAS_SEBAGIAN = 'lunas_sebagian';

    public const PAYMENT_LUNAS = 'lunas';

    public const PAYMENT_DIKEMBALIKAN = 'dikembalikan';

    // Metode serah terima
    public const METHOD_DIAMBIL = 'diambil';

    public const METHOD_DIANTAR = 'diantar';

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'estimated_completion_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'total_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function handoverHistories(): HasMany
    {
        return $this->hasMany(OrderHandoverHistory::class);
    }

    public function priceAdjustments(): HasMany
    {
        return $this->hasMany(OrderPriceAdjustment::class);
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'model');
    }

    /**
     * Sisa tagihan yang belum dibayar, memperhitungkan refund.
     */
    protected function remainingAmount(): Attribute
    {
        return Attribute::get(function () {
            $total = $this->totalInCents();
            $paid = (int) round((float) $this->paid_amount * 100);
            $refunded = $this->relationLoaded('refunds')
                ? (int) round((float) $this->refunds->sum('amount') * 100)
                : (int) round((float) $this->refunds()->sum('amount') * 100);

            return (float) max(0, $total - $paid + $refunded) / 100;
        });
    }

    /**
     * Total tagihan dalam sen.
     */
    public function totalInCents(): int
    {
        return (int) round((float) $this->total_price * 100);
    }

    /**
     * Total pembayaran yang pernah diterima dalam sen (dari kolom otoritatif).
     */
    public function paidInCents(): int
    {
        return (int) round((float) $this->paid_amount * 100);
    }

    /**
     * Total refund yang pernah dikeluarkan dalam sen.
     */
    public function refundedInCents(): int
    {
        return (int) round((float) $this->refunds()->sum('amount') * 100);
    }

    /**
     * Mencatat pembayaran. Pembayaran tidak boleh melebihi sisa tagihan.
     */
    public function recordPayment(int|float $amount, string $paymentMethod, ?string $note = null, ?User $user = null): self
    {
        $amountCents = (int) round((float) $amount * 100);

        if ($amountCents <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal pembayaran harus lebih dari 0.',
            ]);
        }

        if (! in_array($paymentMethod, Payment::methods(), true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'Metode pembayaran tidak valid.',
            ]);
        }

        if ($amountCents > (int) round($this->remainingAmount * 100)) {
            throw ValidationException::withMessages([
                'amount' => 'Pembayaran tidak boleh melebihi sisa tagihan.',
            ]);
        }

        $actor = $user?->id ?? auth()->id();

        DB::transaction(function () use ($amountCents, $paymentMethod, $note, $actor) {
            $this->payments()->create([
                'user_id' => $actor,
                'amount' => $amountCents / 100,
                'payment_method' => $paymentMethod,
                'received_at' => now(),
                'note' => trim((string) $note) ?: null,
            ]);

            $this->paid_amount = (int) round((float) $this->payments()->sum('amount') * 100) / 100;
            $this->payment_method = $paymentMethod;
            $this->payment_status = $this->recomputePaymentStatus();
            $this->save();
        });

        return $this;
    }

    /**
     * Mencatat refund terpisah. Jumlah refund tidak boleh melebihi total
     * pembayaran yang pernah diterima (dikurangi refund sebelumnya).
     */
    public function recordRefund(int|float $amount, string $reason, string $method, ?int $paymentId = null, ?User $user = null): self
    {
        $amountCents = (int) round((float) $amount * 100);
        $reason = trim($reason);

        if ($amountCents <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Nominal refund harus lebih dari 0.',
            ]);
        }

        if ($reason === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan refund wajib diisi.',
            ]);
        }

        if (! in_array($method, Payment::methods(), true)) {
            throw ValidationException::withMessages([
                'method' => 'Metode refund tidak valid.',
            ]);
        }

        $refundableCents = $this->paidInCents() - $this->refundedInCents();

        if ($amountCents > $refundableCents) {
            throw ValidationException::withMessages([
                'amount' => 'Jumlah refund tidak boleh melebihi total pembayaran.',
            ]);
        }

        $actor = $user?->id ?? auth()->id();

        DB::transaction(function () use ($amountCents, $reason, $method, $paymentId, $actor) {
            $this->refunds()->create([
                'payment_id' => $paymentId,
                'amount' => $amountCents / 100,
                'reason' => $reason,
                'method' => $method,
                'processed_by' => $actor,
                'refunded_at' => now(),
            ]);

            $this->payment_status = $this->recomputePaymentStatus();
            $this->save();
        });

        return $this;
    }

    /**
     * Menentukan status pembayaran dari total pembayaran dan refund.
     */
    protected function recomputePaymentStatus(): string
    {
        $paid = $this->payments()->sum('amount');

        if ((int) round((float) $paid * 100) === 0) {
            return self::PAYMENT_BELUM_LUNAS;
        }

        $netCents = (int) round((float) $paid * 100) - $this->refundedInCents();

        if ($netCents <= 0) {
            return self::PAYMENT_DIKEMBALIKAN;
        }

        if ($netCents >= $this->totalInCents()) {
            return self::PAYMENT_LUNAS;
        }

        return self::PAYMENT_LUNAS_SEBAGIAN;
    }

    /**
     * Mencatat perubahan harga final pesanan. Harga baru diterima apa adanya
     * (dihitung di luar aplikasi); yang wajib hanyalah alasan.
     */
    public function adjustFinalPrice(int $newAmount, string $reason, ?User $user = null): self
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'Alasan penyesuaian harga wajib diisi.']);
        }

        $current = (int) round((float) $this->total_price);

        if ($newAmount === $current) {
            return $this;
        }

        $this->priceAdjustments()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'previous_amount' => $current,
            'new_amount' => $newAmount,
            'reason' => $reason,
        ]);

        $this->update(['total_price' => $newAmount]);

        return $this;
    }

    /**
     * Mengubah status pengerjaan ke depan (boleh melompati tahap, tidak
     * boleh mundur). Mencatat status lama, status baru, pengguna, dan waktu.
     */
    public function changeStatus(string $to, ?User $user = null, ?string $note = null): self
    {
        if (! StatusFlow::canTransition((string) $this->status, $to)) {
            throw ValidationException::withMessages([
                'status' => 'Transisi status dari '.self::statusLabel((string) $this->status).' ke '.self::statusLabel($to).' tidak diizinkan.',
            ]);
        }

        $from = $this->status;

        DB::transaction(function () use ($from, $to, $user, $note) {
            $this->update(['status' => $to]);

            $this->statusHistories()->create([
                'from_status' => $from,
                'to_status' => $to,
                'user_id' => $user?->id ?? auth()->id(),
                'note' => trim((string) $note) ?: null,
            ]);
        });

        return $this;
    }

    /**
     * Mencatat serah terima pesanan. Hanya diperbolehkan saat status
     * pengerjaan sudah Siap Diambil. Aksi gagal tidak mengubah status
     * serah terima sehingga delivery dapat dicoba kembali.
     */
    public function recordHandover(
        string $action,
        ?string $note = null,
        ?string $failureReason = null,
        ?string $deliveryAddress = null,
        ?User $user = null,
    ): self {
        $allowed = [
            OrderHandoverHistory::ACTION_SUDAH_DIAMBIL,
            OrderHandoverHistory::ACTION_SUDAH_DIANTAR,
            OrderHandoverHistory::ACTION_GAGAL,
        ];

        if (! in_array($action, $allowed, true)) {
            throw ValidationException::withMessages([
                'handover_action' => 'Aksi serah terima tidak valid.',
            ]);
        }

        if ($this->status !== self::STATUS_SIAP_DIAMBIL) {
            throw ValidationException::withMessages([
                'handover_action' => 'Pesanan harus berstatus Siap Diambil sebelum diserahterimakan.',
            ]);
        }

        if ($action === OrderHandoverHistory::ACTION_GAGAL && trim((string) $failureReason) === '') {
            throw ValidationException::withMessages([
                'failure_reason' => 'Alasan gagal delivery wajib diisi.',
            ]);
        }

        $address = trim((string) ($this->delivery_address ?? ''));
        $address = trim((string) $deliveryAddress) ?: $address;

        if ($action === OrderHandoverHistory::ACTION_SUDAH_DIANTAR && $address === '') {
            throw ValidationException::withMessages([
                'delivery_address' => 'Alamat pengantaran wajib diisi untuk pesanan yang diantar.',
            ]);
        }

        $actor = $user?->id ?? auth()->id();
        $methodReconciled = false;
        $historyNote = trim((string) $note) ?: null;

        if ($action === OrderHandoverHistory::ACTION_SUDAH_DIAMBIL && $this->method !== self::METHOD_DIAMBIL) {
            $this->method = self::METHOD_DIAMBIL;
            $methodReconciled = true;
        }

        if ($action === OrderHandoverHistory::ACTION_SUDAH_DIANTAR && $this->method !== self::METHOD_DIANTAR) {
            $this->method = self::METHOD_DIANTAR;
            $methodReconciled = true;
        }

        if ($methodReconciled) {
            $historyNote = trim(($historyNote ? $historyNote.' — ' : '').'Metode disesuaikan: '.self::methodLabel($this->method)) ?: null;
        }

        DB::transaction(function () use ($action, $historyNote, $failureReason, $address, $actor) {
            if ($action !== OrderHandoverHistory::ACTION_GAGAL) {
                $this->update([
                    'handover_status' => $action,
                    'method' => $this->method,
                    'delivery_address' => $address,
                ]);
            }

            $this->handoverHistories()->create([
                'handover_action' => $action,
                'recorded_by' => $actor,
                'occurred_at' => now(),
                'note' => $historyNote,
                'failure_reason' => trim((string) $failureReason) ?: null,
            ]);
        });

        return $this;
    }

    /**
     * Membatalkan pesanan. Alasan wajib; hanya diperbolehkan selama pesanan
     * masih diproses (belum diserahterimakan) dan belum dibatalkan.
     */
    public function cancel(string $reason, ?User $user = null): self
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw ValidationException::withMessages([
                'cancellation_reason' => 'Alasan pembatalan wajib diisi.',
            ]);
        }

        if ($this->status === self::STATUS_DIBATALKAN) {
            throw ValidationException::withMessages([
                'status' => 'Pesanan sudah dibatalkan.',
            ]);
        }

        if ($this->handover_status !== self::HANDOVER_BELUM_DISERAHKAN) {
            throw ValidationException::withMessages([
                'status' => 'Pesanan yang sudah diserahterimakan tidak dapat dibatalkan.',
            ]);
        }

        $from = $this->status;
        $actor = $user?->id ?? auth()->id();

        DB::transaction(function () use ($reason, $from, $actor, $user) {
            $this->update([
                'status' => self::STATUS_DIBATALKAN,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
                'cancelled_by' => $actor,
            ]);

            $this->statusHistories()->create([
                'from_status' => $from,
                'to_status' => self::STATUS_DIBATALKAN,
                'user_id' => $actor,
                'note' => $reason,
            ]);

            Audit::record($user, 'order.cancelled', $this, [
                'status' => $from,
            ], [
                'status' => self::STATUS_DIBATALKAN,
                'reason' => $reason,
            ]);
        });

        return $this;
    }

    /**
     * Memulihkan pesanan yang dibatalkan (Super Admin). Kembali ke status
     * Diterima; riwayat pembatalan tetap tersimpan di status history dan
     * audit log.
     */
    public function restore(?User $user = null): self
    {
        if ($this->status !== self::STATUS_DIBATALKAN) {
            throw ValidationException::withMessages([
                'status' => 'Hanya pesanan yang dibatalkan yang dapat dipulihkan.',
            ]);
        }

        $actor = $user?->id ?? auth()->id();
        $reasonKept = $this->cancellation_reason;

        DB::transaction(function () use ($actor, $reasonKept, $user) {
            $this->update([
                'status' => self::STATUS_DITERIMA,
                'cancellation_reason' => null,
                'cancelled_at' => null,
                'cancelled_by' => null,
            ]);

            $this->statusHistories()->create([
                'from_status' => self::STATUS_DIBATALKAN,
                'to_status' => self::STATUS_DITERIMA,
                'user_id' => $actor,
                'note' => 'Pesanan dipulihkan',
            ]);

            Audit::record($user, 'order.restored', $this, [
                'status' => self::STATUS_DIBATALKAN,
                'reason' => $reasonKept,
            ], [
                'status' => self::STATUS_DITERIMA,
            ]);
        });

        return $this;
    }

    /**
     * Nomor pesanan berikutnya dengan format {nomor urut}{DDMMYYYY}.
     * Nomor urut dihitung dari pesanan terakhir yang pernah dibuat.
     */
    public static function nextOrderNumber(?Carbon $date = null): string
    {
        $date ??= now();
        $datePart = $date->format('dmY');

        return DB::transaction(function () use ($datePart) {
            $latest = Order::query()->latest('id')->lockForUpdate()->first();
            $sequence = 1;

            if ($latest !== null) {
                $seq = substr((string) $latest->order_number, 0, -8);

                if (is_numeric($seq)) {
                    $sequence = (int) $seq + 1;
                }
            }

            return sprintf('%04d', $sequence).$datePart;
        });
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DITERIMA,
            self::STATUS_SEDANG_DICUCI,
            self::STATUS_SEDANG_DIKERINGKAN,
            self::STATUS_SEDANG_DISETRIKA,
            self::STATUS_SEDANG_DILIPAT,
            self::STATUS_SIAP_DIAMBIL,
            self::STATUS_DIBATALKAN,
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_BELUM_LUNAS,
            self::PAYMENT_LUNAS_SEBAGIAN,
            self::PAYMENT_LUNAS,
            self::PAYMENT_DIKEMBALIKAN,
        ];
    }

    public static function handoverStatuses(): array
    {
        return [
            self::HANDOVER_BELUM_DISERAHKAN,
            self::HANDOVER_SUDAH_DIAMBIL,
            self::HANDOVER_SUDAH_DIANTAR,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DITERIMA => 'Diterima',
            self::STATUS_SEDANG_DICUCI => 'Sedang Dicuci',
            self::STATUS_SEDANG_DIKERINGKAN => 'Sedang Dikeringkan',
            self::STATUS_SEDANG_DISETRIKA => 'Sedang Disetrika',
            self::STATUS_SEDANG_DILIPAT => 'Sedang Dilipat',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_DIBATALKAN => 'Dibatalkan',
            default => $status,
        };
    }

    public static function handoverLabel(string $handoverStatus): string
    {
        return match ($handoverStatus) {
            self::HANDOVER_BELUM_DISERAHKAN => 'Belum Diserahkan',
            self::HANDOVER_SUDAH_DIAMBIL => 'Sudah Diambil',
            self::HANDOVER_SUDAH_DIANTAR => 'Sudah Diantar',
            default => $handoverStatus,
        };
    }

    public static function paymentLabel(string $paymentStatus): string
    {
        return match ($paymentStatus) {
            self::PAYMENT_BELUM_LUNAS => 'Belum Lunas',
            self::PAYMENT_LUNAS_SEBAGIAN => 'Lunas Sebagian',
            self::PAYMENT_LUNAS => 'Lunas',
            self::PAYMENT_DIKEMBALIKAN => 'Dikembalikan',
            default => $paymentStatus,
        };
    }

    public static function methodLabel(string $method): string
    {
        return match ($method) {
            self::METHOD_DIAMBIL => 'Diambil Pelanggan',
            self::METHOD_DIANTAR => 'Diantar',
            default => $method,
        };
    }
}
