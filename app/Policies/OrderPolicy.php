<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return true;
    }

    public function cancel(User $user, Order $order): bool
    {
        return true;
    }

    public function adjustPrice(User $user, Order $order): bool
    {
        return $user->isSuperAdmin();
    }

    public function overrideEstimate(User $user, Order $order): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Order $order): bool
    {
        return $user->isSuperAdmin();
    }
}
