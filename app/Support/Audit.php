<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class Audit
{
    /**
     * Mencatat kejadian penting ke tabel audit_logs (morph).
     *
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public static function record(?User $user, string $action, Model $model, ?array $before = null, ?array $after = null): AuditLog
    {
        return AuditLog::query()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'model_type' => $model->getMorphClass(),
            'model_id' => $model->getKey(),
            'before' => $before,
            'after' => $after,
            'ip_address' => Request::ip(),
        ]);
    }
}
