<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        // Ensure roles are loaded
        if ($user) {
            $user->load('roles');
        }
        
        // Extract only role names as strings
        $userRoles = $user ? $user->roles->pluck('name')->values()->toArray() : [];
        
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'full_name' => $user->full_name,
                    'roles' => $userRoles,
                ] : null,
            ],
            'csrf_token' => csrf_token(),
            'ziggy' => fn() => [
                'location' => \Illuminate\Support\Facades\Route::current()?->uri ?? '',
            ],
        ];
    }
}
