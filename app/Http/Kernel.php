<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     * These can be assigned to routes or used within controllers.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class ?? \Illuminate\Auth\Middleware\Authenticate::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'permission' => \App\Http\Middleware\EnsureHasPermission::class,
    ];
}
