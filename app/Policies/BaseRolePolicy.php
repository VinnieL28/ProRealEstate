<?php

namespace App\Policies;

use App\Models\User;

abstract class BaseRolePolicy
{
    protected array $fullAccess = ['owner', 'admin'];

    protected function canFull(User $user): bool
    {
        return in_array($user->role, $this->fullAccess, true);
    }

    protected function canManage(User $user): bool
    {
        return in_array($user->role, [
            'owner',
            'admin',
            'acquisition_manager',
            'acquisition_sales_manager',
            'lead_manager',
            'lead_manager_verifier',
            'dispo_manager',
            'transaction_coordinator',
            'property_manager',
        ], true);
    }

    protected function canLimited(User $user): bool
    {
        return in_array($user->role, ['cold_caller', 'real_estate_agent'], true);
    }
}
