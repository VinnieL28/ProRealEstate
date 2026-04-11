<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TeamScope;

/**
 * Applying this trait to any model with a team_id column will automatically
 * scope all queries to the authenticated user's team, preventing cross-tenant
 * data leakage. Super admins bypass the scope.
 */
trait HasTeamScope
{
    public static function bootHasTeamScope(): void
    {
        static::addGlobalScope(new TeamScope());
    }
}
