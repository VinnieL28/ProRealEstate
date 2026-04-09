<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canFull($user);
    }

    public function view(User $user, Setting $setting): bool
    {
        return $this->canFull($user);
    }

    public function update(User $user, Setting $setting): bool
    {
        return $this->canFull($user);
    }

    public function create(User $user): bool
    {
        return $this->canFull($user);
    }

    public function delete(User $user, Setting $setting): bool
    {
        return false;
    }
}
