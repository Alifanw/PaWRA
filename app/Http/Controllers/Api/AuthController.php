<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Login user and generate Sanctum token
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Check if account is locked
        if (Cache::has("account_locked:{$credentials['username']}")) {
            return response()->json([
                'error' => 'Account Locked',
                'message' => 'Your account has been temporarily locked due to too many failed login attempts.'
            ], 423);
        }

        // Find user
        $user = User::with('role')
            ->where('username', $credentials['username'])
            ->first();

        // Validate credentials
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Invalid Credentials',
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if user is blocked
        if ($user->is_block) {
            return response()->json([
                'error' => 'Account Blocked',
                'message' => 'Your account has been blocked. Please contact administrator.'
            ], 403);
        }

        // Generate token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Log successful login
        $this->logAudit($user, 'login', $request);

        // Clear permission cache
        Cache::forget("user_permissions_{$user->id}");

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 60) * 60,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $user->role->name ?? null,
            ]
        ], 200);
    }

    /**
     * Logout user and revoke token
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        // Log logout
        $this->logAudit($user, 'logout', $request);

        // Clear permission cache
        Cache::forget("user_permissions_{$user->id}");

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }

    /**
     * Get authenticated user info
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role_name' => $user->role->name ?? null,
                'profile_picture' => $user->profile_picture,
                'last_login_at' => $user->last_login_at,
            ]
        ], 200);
    }

    /**
     * Log audit trail
     */
    protected function logAudit(User $user, string $action, Request $request): void
    {
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'action' => $action,
            'resource' => 'auth',
            'resource_id' => $user->id,
            'ip_addr' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
