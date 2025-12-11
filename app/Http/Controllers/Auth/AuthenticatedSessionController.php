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

        Log::info('Auth: Login successful', [
            'user_id' => $user->id,
            'username' => $user->username,
            'session_id' => session()->getId(),
        ]);

        // Laravel middleware will automatically save session and set cookie with correct ID
        return redirect('/admin/dashboard');
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
