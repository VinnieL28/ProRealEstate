<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canFull($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->canFull($user) || $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $this->canFull($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->canFull($user) || $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $this->canFull($user) && $user->id !== $model->id;
    }

    public function deleteAny(User $user): bool
    {
        return $this->canFull($user);
    }
}
