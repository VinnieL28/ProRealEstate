<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TeamScope implements Scope
{
    /**
     * Apply the scope to automatically filter all queries by the current user's team.
     * Skipped for super_admin users (they can see all teams).
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        // Super admins bypass team scoping
        if ($user->role === 'super_admin') {
            return;
        }

        if ($user->team_id) {
            $builder->where($model->getTable() . '.team_id', $user->team_id);
        }
    }
}
