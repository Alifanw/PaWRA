<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleAuthResponse
{
    /**
     * Handle an incoming request - ensure proper auth response handling.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // If this is a login POST request, ensure proper redirect response
        if ($request->isMethod('POST') && $request->path() === 'login') {
            
            // Check if user is authenticated after the request
            if (auth()->check()) {
                Log::info('Auth Response: User authenticated after login request', [
                    'user_id' => auth()->id(),
                    'username' => auth()->user()->username,
                    'response_status' => $response->getStatusCode(),
                    'is_redirect' => $response->isRedirect(),
                ]);

                // Add headers to prevent caching of auth responses
                $response->header('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->header('Pragma', 'no-cache');
                $response->header('Expires', '0');
            } else {
                // Login failed
                Log::info('Auth Response: Login failed (user not authenticated)', [
                    'username' => $request->input('username'),
                    'response_status' => $response->getStatusCode(),
                ]);
            }
        }

        return $response;
    }
}
