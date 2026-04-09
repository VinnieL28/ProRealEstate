<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy extends BaseRolePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canManage($user) || $this->canLimited($user);
    }

    public function view(User $user, Attachment $attachment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, Attachment $attachment): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $this->canFull($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canFull($user);
    }
}
