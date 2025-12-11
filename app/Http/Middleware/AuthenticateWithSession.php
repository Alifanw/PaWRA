<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithSession
{
    /**
     * Handle an incoming request - Enhanced authentication with session check
     */
    public function handle(Request $request, Closure $next, $guard = null): Response
    {
        $guard = $guard ?? 'web';
        
        // First, check if user is already authenticated via Guard
        if (Auth::guard($guard)->check()) {
            Log::debug('User already authenticated', [
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);
            return $next($request);
        }

        // If not authenticated via Guard, try to load from session data
        $sessionId = session()->getId();
        $userId = session('user_id');
        
        if ($userId) {
            // Try to load the user from database
            try {
                $user = DB::table('users')->find($userId);
                if ($user) {
                    Log::debug('Loading user from session data', [
                        'user_id' => $userId,
                        'session_id' => $sessionId,
                    ]);
                    // Let Laravel auth system handle the user
                    Auth::guard($guard)->loginUsingId($userId, true);
                    return $next($request);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to load user from session', [
                    'error' => $e->getMessage(),
                    'session_id' => $sessionId,
                ]);
            }
        }

        // Not authenticated - redirect to login
        Log::warning('Authentication check failed', [
            'path' => $request->path(),
            'session_id' => $sessionId,
            'has_user_id_in_session' => !is_null($userId),
        ]);

        return redirect('/login');
    }
}
