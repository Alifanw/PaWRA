<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse|HttpResponse
    {
        $request->authenticate();
        
        // Regenerate session for security
        $request->session()->regenerate();
        
        // Get the authenticated user
        $user = auth()->user();
        
        // Explicitly store user data in session
        $request->session()->put('user_id', $user->id);
        $request->session()->put('user_name', $user->username);
        $request->session()->put('user_full_name', $user->full_name ?? $user->name);
        
        // Save session to database immediately
        $request->session()->save();

        $dashboardPath = '/admin/dashboard';

        Log::info('Auth: Login successful, session created and persisted', [
            'user_id' => $user->id,
            'username' => $user->username,
            'session_id' => session()->getId(),
        ]);

        // Temporary debug: dump session contents to log to help diagnose client/session mismatch
        try {
            Log::debug('Auth: Session dump after save', [
                'session_id' => session()->getId(),
                'session_all' => $request->session()->all(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Auth: Failed to dump session', ['error' => $e->getMessage()]);
        }

        // Return HTTP 302 redirect (standard redirect response)
        return redirect($dashboardPath);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
