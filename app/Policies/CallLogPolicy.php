<?php

namespace App\Policies;

use App\Models\CallLog;
use App\Models\User;

class CallLogPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $this->canLimited($user);
    }

    public function view(User $user, CallLog $log): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user) || $user->role === 'cold_caller';
    }

    public function update(User $user, CallLog $log): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, CallLog $log): bool
    {
        return $this->canFull($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canFull($user);
    }
}
