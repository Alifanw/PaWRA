<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TicketSaleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Users & Roles
    Route::middleware('permission:users.manage')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::patch('users/{user}/status', [UserController::class, 'updateStatus']);
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::apiResource('roles', RoleController::class);
    });

    // Products & Categories
    Route::middleware('permission:products.manage,products.view')->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    // Bookings
    Route::middleware('permission:bookings.create,bookings.manage,bookings.view')->group(function () {
        Route::apiResource('bookings', BookingController::class);
        Route::put('bookings/{booking}/status', [BookingController::class, 'updateStatus']);
        Route::post('bookings/{booking}/payments', [BookingController::class, 'addPayment'])
            ->middleware('permission:payments.create');
    });

    // Ticket Sales
    Route::middleware('permission:sales.create,sales.view')->group(function () {
        Route::apiResource('ticket-sales', TicketSaleController::class);
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/daily-sales', [TicketSaleController::class, 'dailySalesReport']);
        Route::get('reports/bookings', [BookingController::class, 'bookingReport']);
    });
});
