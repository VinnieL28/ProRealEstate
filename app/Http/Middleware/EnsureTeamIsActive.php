<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeamIsActive
{
    /**
     * Lock out users whose team has no active subscription and trial has expired.
     * Super admins always bypass this check.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Not logged in, or super admin — pass through
        if (!$user || $user->isSuperAdmin()) {
            return $next($request);
        }

        // Allow access to billing-related pages and logout
        $allowed = [
            '/admin/billing',
            '/admin/logout',
            '/logout',
            '/admin/login',
        ];

        foreach ($allowed as $path) {
            if ($request->is(ltrim($path, '/') . '*')) {
                return $next($request);
            }
        }

        $team = $user->team;

        if (!$team) {
            return $next($request);
        }

        if (!$team->hasActiveAccess()) {
            // Redirect to paywall
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your subscription has expired. Please upgrade to continue.'], 402);
            }

            return redirect('/admin/billing')->with('warning', 'Your trial has ended. Please subscribe to continue using the CRM.');
        }

        return $next($request);
    }
}
