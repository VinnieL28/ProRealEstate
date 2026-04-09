<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $this->canLimited($user);
    }

    public function view(User $user, Lead $lead): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user) || $user->role === 'cold_caller';
    }

    public function update(User $user, Lead $lead): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $this->canFull($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canFull($user);
    }
}
