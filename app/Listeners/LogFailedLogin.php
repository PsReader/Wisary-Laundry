<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Failed;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        LoginActivity::query()->create([
            'user_id' => $event->user?->getKey(),
            'type' => LoginActivity::TYPE_FAILED,
            'email' => $event->credentials['email'] ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
