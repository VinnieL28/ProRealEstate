<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $this->canLimited($user);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->canFull($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canFull($user);
    }
}
