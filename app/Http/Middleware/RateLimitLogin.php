<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    /**
     * Handle an incoming request.
     * Rate limit login attempts by IP and username
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->input('username');
        $ip = $request->ip();
        
        // Rate limit by IP: max 5 attempts per minute
        $ipKey = 'login_attempts_ip:' . $ip;
        if (RateLimiter::tooManyAttempts($ipKey, 5)) {
            $seconds = RateLimiter::availableIn($ipKey);
            
            return response()->json([
                'error' => 'Too Many Attempts',
                'message' => "Too many login attempts. Please try again in {$seconds} seconds.",
                'retry_after' => $seconds
            ], 429);
        }

        // Rate limit by username: max 3 attempts per 5 minutes
        if ($username) {
            $usernameKey = 'login_attempts_user:' . $username;
            if (RateLimiter::tooManyAttempts($usernameKey, 3)) {
                $seconds = RateLimiter::availableIn($usernameKey);
                
                // Lock account if too many attempts
                $this->lockAccount($username);
                
                return response()->json([
                    'error' => 'Account Locked',
                    'message' => 'Too many failed login attempts. Account has been temporarily locked.',
                    'retry_after' => $seconds
                ], 429);
            }
        }

        $response = $next($request);

        // On failed login, increment counters
        if ($response->status() === 401) {
            RateLimiter::hit($ipKey, 60); // 1 minute decay
            if ($username) {
                RateLimiter::hit($usernameKey, 300); // 5 minutes decay
            }
        }

        // On successful login, clear counters
        if ($response->status() === 200) {
            RateLimiter::clear($ipKey);
            if ($username) {
                RateLimiter::clear($usernameKey);
            }
        }

        return $response;
    }

    /**
     * Lock user account temporarily
     */
    protected function lockAccount(string $username): void
    {
        Cache::put(
            "account_locked:{$username}",
            true,
            now()->addMinutes(15)
        );
    }
}
