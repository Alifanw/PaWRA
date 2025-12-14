<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles  Allowed roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect('/login');
        }

        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // If no roles specified, allow access
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has at least one allowed role
        $hasAccess = false;
        foreach ($userRoles as $userRole) {
            if (in_array($userRole, $roles)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            // Redirect to appropriate dashboard based on user role
            return $this->redirectByRole($userRoles);
        }

        return $next($request);
    }

    /**
     * Redirect user to their appropriate dashboard based on role
     */
    private function redirectByRole(array $userRoles)
    {
        // Priority order
        if (in_array('ticketing', $userRoles)) {
            return redirect('/admin/ticket-sales');
        }
        if (in_array('booking', $userRoles)) {
            return redirect('/admin/bookings');
        }
        if (in_array('parking', $userRoles)) {
            return redirect('/admin/parking');
        }
        if (in_array('monitoring', $userRoles)) {
            // There is no /admin/monitor route — send monitoring users to dashboard
            return redirect('/admin/dashboard');
        }
        if (in_array('admin', $userRoles) || in_array('superadmin', $userRoles)) {
            return redirect('/admin/dashboard');
        }

        return redirect('/admin/dashboard');
    }
}
