<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PreventAuthRedirectLoop
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Track redirect attempts to prevent loops
        $redirectCount = session('_auth_redirect_count', 0);
        
        if ($redirectCount > 5) {
            Log::warning('Auth: Too many redirects detected', [
                'redirect_count' => $redirectCount,
                'user_id' => auth()->id(),
                'path' => $request->path(),
            ]);
            
            // Reset counter and redirect to home
            session()->forget('_auth_redirect_count');
            return redirect('/');
        }

        // Increment counter for this request
        session(['_auth_redirect_count' => $redirectCount + 1]);

        $response = $next($request);

        // Reset counter on successful response
        if ($response->getStatusCode() < 400) {
            session()->forget('_auth_redirect_count');
        }

        return $response;
    }
}
