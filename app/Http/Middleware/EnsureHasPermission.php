<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureHasPermission
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('permission:manage_users')
     * Usage (multiple): ->middleware('permission:manage_users,view_users')
     * Comma-separated perms = OR logic (user needs one of them)
     */
    public function handle(Request $request, Closure $next, $permissions)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message'=>'Unauthenticated'], 401);
        }

        // Superadmin bypass
        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        // Check permissions (comma-separated = OR logic)
        $requiredPerms = array_map('trim', explode(',', $permissions));
        foreach ($requiredPerms as $perm) {
            if ($user->hasPermission($perm)) {
                return $next($request);
            }
        }

        return response()->json(['message'=>'Forbidden: insufficient permissions'], 403);
    }
}
