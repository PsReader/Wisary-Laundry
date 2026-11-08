<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\LoginActivity;
use App\Models\Order;
use App\Models\OrderHandoverHistory;
use App\Models\OrderPriceAdjustment;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AuditFeed
{
    public const GROUP_ORDER_CREATED = 'order.created';

    public const GROUP_ORDER_STATUS = 'order.status';

    public const GROUP_ORDER_HANDOVER = 'order.handover';

    public const GROUP_ORDER_PAYMENT = 'order.payment';

    public const GROUP_ORDER_REFUND = 'order.refund';

    public const GROUP_ORDER_PRICE_ADJUST = 'order.price_adjust';

    public const GROUP_ORDER_ESTIMATE = 'order.estimate_overridden';

    public const GROUP_CUSTOMER = 'customer';

    public const GROUP_SERVICE = 'service';

    public const GROUP_USER = 'user';

    public const GROUP_LOGIN = 'login';

    public const GROUP_LOGOUT = 'logout';

    public const GROUP_LOGIN_FAILED = 'login_failed';

    /**
     * Opsi filter jenis aksi untuk tampilan audit log.
     *
     * @return array<string, string> Filter value mapped to its Indonesian label.
     */
    public static function groupOptions(): array
    {
        return [
            self::GROUP_ORDER_CREATED => 'Pesanan Dibuat',
            self::GROUP_ORDER_STATUS => 'Perubahan Status',
            self::GROUP_ORDER_HANDOVER => 'Serah Terima',
            self::GROUP_ORDER_PAYMENT => 'Pembayaran',
            self::GROUP_ORDER_REFUND => 'Refund',
            self::GROUP_ORDER_PRICE_ADJUST => 'Penyesuaian Harga',
            self::GROUP_ORDER_ESTIMATE => 'Ubah Estimasi',
            self::GROUP_CUSTOMER => 'Pelanggan',
            self::GROUP_SERVICE => 'Layanan',
            self::GROUP_USER => 'Akun Staf',
            self::GROUP_LOGIN => 'Login',
            self::GROUP_LOGOUT => 'Logout',
            self::GROUP_LOGIN_FAILED => 'Login Gagal',
        ];
    }

    /**
     * Menggabungkan seluruh sumber riwayat menjadi satu alur audit.
     * Baris ternormalkan, diurutkan menurun berdasarkan waktu.
     *
     * @param  int  $limit  batas baris per sumber sebelum digabung
     * @return Collection<int, array{
     *     at: Carbon, userId: ?int, user: string, type: string, detail: string,
     *     orderId: ?int, orderNumber: ?string
     * }>
     */
    public static function feed(
        Carbon $from,
        Carbon $to,
        ?int $userId = null,
        ?string $group = null,
        int $limit = 500,
    ): Collection {
        $rows = collect();

        $includesGroup = fn (?string $currentGroup) => $group === null || $group === $currentGroup;
        $matchesUser = fn (?int $actorId) => $userId === null || $userId === $actorId;

        if ($includesGroup(self::GROUP_ORDER_CREATED)) {
            $orders = Order::query()
                ->with(['createdBy'])
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($orders as $order) {
                if (! $matchesUser($order->created_by)) {
                    continue;
                }

                $rows->push(self::buildFeedEntry(
                    at: $order->created_at,
                    userId: $order->created_by,
                    userName: $order->createdBy?->name,
                    type: self::GROUP_ORDER_CREATED,
                    detail: 'Pesanan dibuat No. '.$order->order_number.' · total '.format_rupiah((float) $order->total_price),
                    orderId: $order->id,
                    orderNumber: $order->order_number,
                ));
            }
        }

        if ($includesGroup(self::GROUP_ORDER_STATUS)) {
            $histories = OrderStatusHistory::query()
                ->with(['user', 'order'])
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($histories as $history) {
                $order = $history->order;

                if (! $matchesUser($history->user_id)) {
                    continue;
                }

                $transition = Order::statusLabel((string) $history->from_status).' → '.Order::statusLabel((string) $history->to_status);
                $note = trim((string) $history->note);
                $detail = $note !== '' && $history->to_status === Order::STATUS_DIBATALKAN
                    ? 'Pesanan dibatalkan: '.$note
                    : $note;

                $rows->push(self::buildFeedEntry(
                    at: $history->created_at,
                    userId: $history->user_id,
                    userName: $history->user?->name,
                    type: self::GROUP_ORDER_STATUS,
                    detail: ($detail === '' ? $transition : $transition.' · '.$detail),
                    orderId: $order?->id,
                    orderNumber: $order?->order_number,
                ));
            }
        }

        if ($includesGroup(self::GROUP_ORDER_HANDOVER)) {
            $events = OrderHandoverHistory::query()
                ->with(['recordedBy', 'order'])
                ->whereBetween('occurred_at', [$from, $to])
                ->orderByDesc('occurred_at')
                ->limit($limit)
                ->get();

            foreach ($events as $event) {
                if (! $matchesUser($event->recorded_by)) {
                    continue;
                }

                $label = OrderHandoverHistory::actionLabel((string) $event->handover_action);
                $reason = trim((string) $event->failure_reason);
                $detail = $event->handover_action === OrderHandoverHistory::ACTION_GAGAL && $reason !== ''
                    ? 'Pengantaran gagal: '.$reason
                    : 'Serah terima: '.$label;

                $rows->push(self::buildFeedEntry(
                    at: $event->occurred_at,
                    userId: $event->recorded_by,
                    userName: $event->recordedBy?->name,
                    type: self::GROUP_ORDER_HANDOVER,
                    detail: $detail,
                    orderId: $event->order_id,
                    orderNumber: $event->order?->order_number,
                ));
            }
        }

        if ($includesGroup(self::GROUP_ORDER_PAYMENT)) {
            $payments = Payment::query()
                ->with(['user', 'order'])
                ->whereBetween('received_at', [$from, $to])
                ->orderByDesc('received_at')
                ->limit($limit)
                ->get();

            foreach ($payments as $payment) {
                if (! $matchesUser($payment->user_id)) {
                    continue;
                }

                $rows->push(self::buildFeedEntry(
                    at: $payment->received_at,
                    userId: $payment->user_id,
                    userName: $payment->user?->name,
                    type: self::GROUP_ORDER_PAYMENT,
                    detail: 'Pembayaran '.Payment::methodLabel((string) $payment->payment_method).' '.format_rupiah((float) $payment->amount),
                    orderId: $payment->order_id,
                    orderNumber: $payment->order?->order_number,
                ));
            }
        }

        if ($includesGroup(self::GROUP_ORDER_REFUND)) {
            $refunds = Refund::query()
                ->with(['processedBy', 'order'])
                ->whereBetween('refunded_at', [$from, $to])
                ->orderByDesc('refunded_at')
                ->limit($limit)
                ->get();

            foreach ($refunds as $refund) {
                if (! $matchesUser($refund->processed_by)) {
                    continue;
                }

                $rows->push(self::buildFeedEntry(
                    at: $refund->refunded_at,
                    userId: $refund->processed_by,
                    userName: $refund->processedBy?->name,
                    type: self::GROUP_ORDER_REFUND,
                    detail: 'Refund '.format_rupiah((float) $refund->amount).' · '.$refund->reason,
                    orderId: $refund->order_id,
                    orderNumber: $refund->order?->order_number,
                ));
            }
        }

        if ($includesGroup(self::GROUP_ORDER_PRICE_ADJUST)) {
            $adjustments = OrderPriceAdjustment::query()
                ->with(['user', 'order'])
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($adjustments as $adjustment) {
                if (! $matchesUser($adjustment->user_id)) {
                    continue;
                }

                $rows->push(self::buildFeedEntry(
                    at: $adjustment->created_at,
                    userId: $adjustment->user_id,
                    userName: $adjustment->user?->name,
                    type: self::GROUP_ORDER_PRICE_ADJUST,
                    detail: 'Harga final: '.format_rupiah((float) $adjustment->previous_amount).' → '.format_rupiah((float) $adjustment->new_amount).' · '.$adjustment->reason,
                    orderId: $adjustment->order_id,
                    orderNumber: $adjustment->order?->order_number,
                ));
            }
        }

        if (
            $includesGroup(self::GROUP_ORDER_ESTIMATE)
            || $includesGroup(self::GROUP_CUSTOMER)
            || $includesGroup(self::GROUP_SERVICE)
            || $includesGroup(self::GROUP_USER)
        ) {
            $auditLogs = AuditLog::query()
                ->with(['user'])
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($auditLogs as $log) {
                $type = self::auditGroup($log->action);

                if ($type === null || ! $includesGroup($type) || ! $matchesUser($log->user_id)) {
                    continue;
                }

                $rows->push(self::buildFeedEntry(
                    at: $log->created_at,
                    userId: $log->user_id,
                    userName: $log->user?->name,
                    type: $type,
                    detail: self::auditDetail($log),
                    orderId: $log->model_type === Order::class ? $log->model_id : null,
                    orderNumber: null,
                ));
            }
        }

        if (
            $includesGroup(self::GROUP_LOGIN)
            || $includesGroup(self::GROUP_LOGOUT)
            || $includesGroup(self::GROUP_LOGIN_FAILED)
        ) {
            $activities = LoginActivity::query()
                ->with(['user'])
                ->whereBetween('created_at', [$from, $to])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            foreach ($activities as $activity) {
                $type = self::loginGroup($activity->type);

                if ($type === null || ! $includesGroup($type) || ! $matchesUser($activity->user_id)) {
                    continue;
                }

                $who = $activity->user?->name ?? $activity->email ?? '-';

                $detail = match ($activity->type) {
                    LoginActivity::TYPE_LOGIN => 'Berhasil masuk · '.self::shortAgent((string) $activity->user_agent),
                    LoginActivity::TYPE_LOGOUT => 'Keluar dari aplikasi',
                    default => 'Percobaan login gagal untuk '.$who,
                };

                $rows->push(self::buildFeedEntry(
                    at: $activity->created_at,
                    userId: $activity->user_id,
                    userName: $activity->user?->name,
                    type: $type,
                    detail: $detail,
                ));
            }
        }

        return $rows->sortByDesc('at')->take($limit)->values();
    }

    /**
     * Kelompok jenis aksi untuk entri audit_logs.
     */
    private static function auditGroup(string $action): ?string
    {
        return match (true) {
            str_starts_with($action, 'customer.') => self::GROUP_CUSTOMER,
            str_starts_with($action, 'service.') => self::GROUP_SERVICE,
            str_starts_with($action, 'user.') => self::GROUP_USER,
            str_starts_with($action, 'order.estimate') => self::GROUP_ORDER_ESTIMATE,
            default => null,
        };
    }

    /**
     * Kelompok jenis aksi untuk entri login_activities.
     */
    private static function loginGroup(string $type): ?string
    {
        return match ($type) {
            LoginActivity::TYPE_LOGIN => self::GROUP_LOGIN,
            LoginActivity::TYPE_LOGOUT => self::GROUP_LOGOUT,
            LoginActivity::TYPE_FAILED => self::GROUP_LOGIN_FAILED,
            default => null,
        };
    }

    /**
     * Ringkasan keterbacaan entri audit_logs.
     */
    private static function auditDetail(AuditLog $log): string
    {
        $after = $log->after ?? [];
        $before = $log->before ?? [];
        $fallbackName = $log->model?->name;

        $priceChanged = array_key_exists('price', $before) && array_key_exists('price', $after) && $before['price'] != $after['price'];

        return match (true) {
            str_starts_with($log->action, 'customer.') => 'Pelanggan: '.($after['name'] ?? $before['name'] ?? $fallbackName ?? '-'),
            str_starts_with($log->action, 'service.') => 'Layanan: '.($after['name'] ?? $before['name'] ?? $fallbackName ?? '-').($priceChanged ? ' · harga '.format_rupiah((float) $before['price']).' → '.format_rupiah((float) $after['price']) : ''),
            str_starts_with($log->action, 'user.') => 'Akun: '.($after['name'] ?? $before['name'] ?? $fallbackName ?? '-'),
            $log->action === 'order.estimate_overridden' => 'Estimasi selesai: '.($before['estimated_completion_at'] ?? '-').' → '.($after['estimated_completion_at'] ?? '-'),
            default => $log->action,
        };
    }

    /**
     * Ringkasan singkat user agent untuk baris login.
     */
    private static function shortAgent(string $userAgent): string
    {
        if (str_contains($userAgent, 'Mobile')) {
            return 'perangkat seluler';
        }

        return 'perangkat';
    }

    /**
     * @return array{
     *     at: Carbon, userId: ?int, user: string, type: string, detail: string,
     *     orderId: ?int, orderNumber: ?string
     * }
     */
    private static function buildFeedEntry(
        Carbon $at,
        ?int $userId,
        ?string $userName,
        string $type,
        string $detail,
        ?int $orderId = null,
        ?string $orderNumber = null,
    ): array {
        return [
            'at' => $at,
            'userId' => $userId,
            'user' => trim((string) $userName) !== '' ? (string) $userName : '-',
            'type' => $type,
            'detail' => $detail,
            'orderId' => $orderId,
            'orderNumber' => $orderNumber,
        ];
    }
}
