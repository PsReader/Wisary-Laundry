<?php

namespace App\Listeners;

use App\Models\LoginActivity;
use Illuminate\Auth\Events\Logout;

class LogSuccessfulLogout
{
    public function handle(Logout $event): void
    {
        LoginActivity::query()->create([
            'user_id' => $event->user?->getKey(),
            'type' => LoginActivity::TYPE_LOGOUT,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
