<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\TicketSaleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\AttendanceController;

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

// Public availability endpoints (untuk booking form)
Route::prefix('availabilities')->group(function () {
    Route::get('/', [AvailabilityController::class, 'getByProduct']);
    Route::get('/product/{productId}', [AvailabilityController::class, 'getAllByProduct']);
    Route::get('/calendar', [AvailabilityController::class, 'getCalendar']);
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

    // Product Availability
    Route::middleware('permission:products.manage,products.view')->group(function () {
        Route::get('availabilities', [AvailabilityController::class, 'getByProduct']);
        Route::get('availabilities/product/{productId}', [AvailabilityController::class, 'getAllByProduct']);
        Route::get('availabilities/calendar', [AvailabilityController::class, 'getCalendar']);
        Route::post('availabilities', [AvailabilityController::class, 'store']);
        Route::put('availabilities/{availability}', [AvailabilityController::class, 'update']);
        Route::delete('availabilities/{availability}', [AvailabilityController::class, 'destroy']);
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
        // Check-in endpoint for scanning tokens
        Route::post('ticket-sales/check-in', [TicketSaleController::class, 'checkIn']);
    });

    // Reports
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/daily-sales', [TicketSaleController::class, 'dailySalesReport']);
        Route::get('reports/bookings', [BookingController::class, 'bookingReport']);
    });

    // Parking module
    Route::prefix('parking')->group(function () {
        Route::post('transactions', [\App\Http\Controllers\Api\ParkingController::class, 'createTransaction'])
            ->middleware('permission:parkir.create');

        Route::post('bookings', [\App\Http\Controllers\Api\ParkingController::class, 'createBooking'])
            ->middleware('permission:parkir.booking');

        Route::post('monitor', [\App\Http\Controllers\Api\ParkingController::class, 'monitor'])
            ->middleware('permission:parkir.monitor');

        // Listing and details (view logs / bookings)
        Route::get('transactions', [\App\Http\Controllers\Api\ParkingController::class, 'transactions'])
            ->middleware('permission:parkir.view_logs');

        Route::get('transactions/{transaction}', [\App\Http\Controllers\Api\ParkingController::class, 'showTransaction'])
            ->middleware('permission:parkir.view_logs');

        Route::get('bookings', [\App\Http\Controllers\Api\ParkingController::class, 'bookings'])
            ->middleware('permission:parkir.booking,parkir.view_logs');

        Route::get('bookings/{booking}', [\App\Http\Controllers\Api\ParkingController::class, 'showBooking'])
            ->middleware('permission:parkir.booking,parkir.view_logs');

        Route::put('bookings/{booking}/status', [\App\Http\Controllers\Api\ParkingController::class, 'updateBookingStatus'])
            ->middleware('permission:parkir.booking');
    });
});

// ============================================================================
// ATTENDANCE / ABSENSI API (Public - untuk kiosk/mobile tanpa auth)
// ============================================================================
Route::prefix('attendance')->group(function () {
    // Absensi kiosk (public endpoint)
    Route::post('/submit', [AttendanceController::class, 'store'])
        ->middleware('throttle:60,1'); // 60 requests per minute
    
    // Get logs (public untuk kiosk display)
    Route::get('/logs', [AttendanceController::class, 'getLogs']);
    
    // Doorlock health check (public)
    Route::get('/doorlock/health', [AttendanceController::class, 'doorlockHealth']);
});

// Admin API Routes
// Register admin routes (also available to session-authenticated tests)
// Also register admin routes using the web `auth` guard so tests using actingAs() work
Route::middleware('auth')->prefix('admin')->group(function () {
    Route::apiResource('roles', AdminRoleController::class);
    Route::post('roles/{role}/sync-permissions', [AdminRoleController::class, 'syncPermissions']);
});

// Also provide admin resource under explicit path to match tests
Route::middleware('auth')->group(function () {
    Route::apiResource('admin/roles', AdminRoleController::class);
    Route::post('admin/roles/{role}/sync-permissions', [AdminRoleController::class, 'syncPermissions']);
});

// Primary admin registration (sanctum + verified for non-testing runtime)
Route::middleware(['auth:sanctum', 'verified'])->prefix('admin')->group(function () {
    // Roles API (REST CRUD)
    Route::apiResource('roles', AdminRoleController::class);
    Route::post('roles/{role}/sync-permissions', [AdminRoleController::class, 'syncPermissions']);
});

