<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Service $service): bool
    {
        return $user->isSuperAdmin();
    }

    public function toggle(User $user, Service $service): bool
    {
        return $user->isSuperAdmin();
    }
}
