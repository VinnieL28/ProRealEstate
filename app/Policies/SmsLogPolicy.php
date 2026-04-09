<?php

namespace App\Policies;

use App\Models\SmsLog;
use App\Models\User;

class SmsLogPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $this->canLimited($user);
    }

    public function view(User $user, SmsLog $log): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user) || $user->role === 'cold_caller';
    }

    public function update(User $user, SmsLog $log): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, SmsLog $log): bool
    {
        return $this->canFull($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canFull($user);
    }
}
