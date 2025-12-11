<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Check if authenticated user has required permission(s)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ], 401);
        }

        // Get user permissions (cached for 5 minutes)
        $userPermissions = Cache::remember(
            "user_permissions_{$user->id}",
            300,
            fn() => $this->getUserPermissions($user->id)
        );

        // Superadmin wildcard check
        if (in_array('*', $userPermissions)) {
            return $next($request);
        }

        // Check if user has at least one required permission
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                return $next($request);
            }
        }

        // Log unauthorized access attempt
        $this->logUnauthorizedAttempt($request, $user, $permissions);

        return response()->json([
            'error' => 'Forbidden',
            'message' => 'You do not have permission to perform this action'
        ], 403);
    }

    /**
     * Get user permissions from role_permissions
     */
    protected function getUserPermissions(int $userId): array
    {
        $permissions = DB::table('users')
            ->join('roles', 'users.role_id', '=', 'roles.id')
            ->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
            ->where('users.id', $userId)
            ->where('users.is_block', false)
            ->pluck('role_permissions.permission')
            ->toArray();

        return $permissions;
    }

    /**
     * Log unauthorized access attempt for audit
     */
    protected function logUnauthorizedAttempt(Request $request, $user, array $permissions): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'action' => 'unauthorized_access_attempt',
            'resource' => $request->path(),
            'resource_id' => null,
            'before_json' => json_encode([
                'required_permissions' => $permissions,
                'method' => $request->method(),
            ]),
            'ip_addr' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
