<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'ip_address', 'user_agent', 'email', 'created_at'])]
#[Hidden(['user_agent'])]
class LoginActivity extends Model
{
    public const TYPE_LOGIN = 'login';

    public const TYPE_FAILED = 'failed';

    public const TYPE_LOGOUT = 'logout';

    public const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
