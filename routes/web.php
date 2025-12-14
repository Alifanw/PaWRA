<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductPageController;
use App\Http\Controllers\Admin\ProductAvailabilityController;
use App\Http\Controllers\Admin\ProductCodeController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\TicketSaleController;
use App\Http\Controllers\Admin\ParkingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BookingPaymentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ReportBookingController;
use App\Http\Controllers\Admin\ReportTicketController;
use App\Http\Controllers\Admin\ReportParkingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Middleware\RestrictByRole;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

use Inertia\Inertia;
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'laravelVersion' => app()->version(),
        'phpVersion' => phpversion(),
    ]);
});

// (debug routes removed)

// Admin routes with auth middleware
Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
    
    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // TICKETING ROLE - Only Ticket Sales + Dashboard
    Route::middleware([RestrictByRole::class . ':ticketing,admin,superadmin'])->group(function () {
        // Ticket Sales
        Route::get('/ticket-sales', [TicketSaleController::class, 'index'])->name('ticket-sales.index');
        Route::get('/ticket-sales/create', [TicketSaleController::class, 'create'])->name('ticket-sales.create');
        Route::post('/ticket-sales', [TicketSaleController::class, 'store'])->name('ticket-sales.store');
        Route::delete('/ticket-sales/bulk-delete', [TicketSaleController::class, 'bulkDestroy'])->name('ticket-sales.bulk-delete');
        Route::get('/ticket-sales/{ticketSale}', [TicketSaleController::class, 'show'])->name('ticket-sales.show');
        Route::get('/ticket-sales/{ticketSale}/print', [TicketSaleController::class, 'print'])->name('ticket-sales.print');
        Route::post('/ticket-sales/{ticketSale}/pay', [TicketSaleController::class, 'pay'])->name('ticket-sales.pay');
        Route::post('/ticket-sales/{ticketSale}/refund', [TicketSaleController::class, 'refund'])->name('ticket-sales.refund');
        Route::post('/ticket-sales/{ticketSale}/cancel', [TicketSaleController::class, 'cancel'])->name('ticket-sales.cancel');

        // Ticket Reports
        Route::get('/reports/ticket-report', [ReportTicketController::class, 'index'])->name('ticket-reports.index');
        Route::get('/reports/ticket-report/export', [ReportTicketController::class, 'export'])->name('ticket-reports.export');
    });

    // BOOKING ROLE - Only Bookings + Dashboard
    Route::middleware([RestrictByRole::class . ':booking,admin,superadmin'])->group(function () {
        // Bookings
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
        Route::delete('/bookings/bulk-delete', [BookingController::class, 'bulkDestroy'])->name('bookings.bulk-delete');
        Route::get('/bookings/{id}/print', [BookingController::class, 'print'])->name('bookings.print');
        Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/payments', [BookingPaymentController::class, 'store'])->name('bookings.payments.store');
        Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');
        Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');

        // Payments - print receipt after payment
        Route::get('/payments/{payment}/print', [BookingPaymentController::class, 'print'])->name('payments.print');

        // Booking Reports
        Route::get('/reports/booking-report', [ReportBookingController::class, 'index'])->name('booking-reports.index');
        Route::get('/reports/booking-report/export', [ReportBookingController::class, 'export'])->name('booking-reports.export');
    });

    // PARKING ROLE - Only Parking + Dashboard
    Route::middleware([RestrictByRole::class . ':parking,superadmin'])->group(function () {
        // Parking Reports
        Route::get('/reports/parking-report', [ReportParkingController::class, 'index'])->name('parking-reports.index');
        Route::get('/reports/parking-report/export', [ReportParkingController::class, 'export'])->name('parking-reports.export');
        // Parking Reports
        Route::get('/reports/parking-report', [ReportParkingController::class, 'index'])->name('parking-reports.index');
        Route::get('/reports/parking-report/export', [ReportParkingController::class, 'export'])->name('parking-reports.export');
        // Parking admin pages
        Route::get('/parking', [\App\Http\Controllers\Admin\ParkingController::class, 'index'])->name('parking.index');
        Route::get('/parking/create', [\App\Http\Controllers\Admin\ParkingController::class, 'create'])->name('parking.create');
        Route::post('/parking', [\App\Http\Controllers\Admin\ParkingController::class, 'store'])->name('parking.store');
        Route::get('/parking/bookings', [\App\Http\Controllers\Admin\ParkingController::class, 'bookings'])->name('parking.bookings');
        Route::get('/parking/monitor', [\App\Http\Controllers\Admin\ParkingController::class, 'monitor'])->name('parking.monitor');
        Route::delete('/parking/bulk-delete', [\App\Http\Controllers\Admin\ParkingController::class, 'bulkDestroy'])->name('parking.bulk-delete');

        // Detail & print routes
        Route::get('/parking/transactions/{transaction}', [\App\Http\Controllers\Admin\ParkingController::class, 'showTransaction'])->name('parking.transactions.show');
        Route::get('/parking/transactions/{transaction}/print', [\App\Http\Controllers\Admin\ParkingController::class, 'printTransaction'])->name('parking.transactions.print');

        Route::get('/parking/bookings/{booking}', [\App\Http\Controllers\Admin\ParkingController::class, 'showBooking'])->name('parking.bookings.show');
        Route::get('/parking/bookings/{booking}/print', [\App\Http\Controllers\Admin\ParkingController::class, 'printBooking'])->name('parking.bookings.print');
    });

    // MONITOR ROLE - Products, Users, Roles, Reports, Audit Logs (NO Ticket Sales, Bookings, Parking)
    Route::middleware([RestrictByRole::class . ':monitoring,superadmin'])->group(function () {
        // Products - Master Data
        Route::get('/products', [ProductPageController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductPageController::class, 'store'])->name('products.store');
        Route::delete('/products/bulk-delete', [ProductPageController::class, 'bulkDestroy'])->name('products.bulk-delete');
        Route::put('/products/{product}', [ProductPageController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductPageController::class, 'destroy'])->name('products.destroy');

        // Product Availability Management (Units/Rooms) 
        Route::get('/products/{product}/availability', [ProductAvailabilityController::class, 'index'])->name('products.availability.index');
        Route::post('/products/{product}/availability', [ProductAvailabilityController::class, 'store'])->name('products.availability.store');
        Route::put('/products/{product}/availability/{availability}', [ProductAvailabilityController::class, 'update'])->name('products.availability.update');
        Route::delete('/products/{product}/availability/{availability}', [ProductAvailabilityController::class, 'destroy'])->name('products.availability.destroy');
        Route::post('/products/{product}/availability/bulk-status', [ProductAvailabilityController::class, 'bulkUpdateStatus'])->name('products.availability.bulk-status');

        // Product Codes - Physical Units Management
        Route::get('/product-codes', [ProductCodeController::class, 'index'])->name('product-codes.index');
        Route::post('/product-codes', [ProductCodeController::class, 'store'])->name('product-codes.store');
        Route::post('/product-codes/bulk-status', [ProductCodeController::class, 'bulkUpdateStatus'])->name('product-codes.bulk-update-status');
        Route::delete('/product-codes/bulk-destroy', [ProductCodeController::class, 'bulkDestroy'])->name('product-codes.bulk-destroy');
        Route::put('/product-codes/{productCode}', [ProductCodeController::class, 'update'])->name('product-codes.update');
        Route::delete('/product-codes/{productCode}', [ProductCodeController::class, 'destroy'])->name('product-codes.destroy');

        // NOTE: Users and Roles management moved to admin/superadmin group only

        // Reports
        Route::get('/reports/all-transactions', [ReportController::class, 'allTransactions'])->name('reports.all-transactions');
        Route::get('/reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
        Route::get('/reports/ticket-sales', [ReportController::class, 'ticketSales'])->name('reports.ticket-sales');
        Route::delete('/reports/bulk-delete', [ReportController::class, 'bulkDelete'])->name('reports.bulk-delete');
        Route::delete('/reports/ticket-sales-bulk-delete', [ReportController::class, 'bulkDeleteTicketSales'])->name('reports.ticket-sales-bulk-delete');
        Route::delete('/reports/bookings-bulk-delete', [ReportController::class, 'bulkDeleteBookings'])->name('reports.bookings-bulk-delete');
        Route::get('/reports/export-all', [ReportController::class, 'exportAll'])->name('reports.export-all');
        Route::get('/reports/export-all-xlsx', [ReportController::class, 'exportAllXlsx'])->name('reports.export-all-xlsx');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::delete('/audit-logs/bulk-delete', [AuditLogController::class, 'bulkDestroy'])->name('audit-logs.bulk-delete');

        // Attendance / Absensi
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/logs', [AttendanceController::class, 'getLogs'])->name('attendance.logs');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        
        // Doorlock Control
        Route::post('/doorlock/test', [AttendanceController::class, 'testDoorlock'])->name('doorlock.test');
        Route::get('/doorlock/status', [AttendanceController::class, 'doorlockStatus'])->name('doorlock.status');
        Route::get('/doorlock/health', [AttendanceController::class, 'doorlockHealth'])->name('doorlock.health');
    });

    // ADMIN + SUPERADMIN - Admin Pages that require elevated privileges
    Route::middleware([RestrictByRole::class . ':admin,superadmin'])->group(function () {
        // Products - Master Data
        Route::get('/products', [ProductPageController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductPageController::class, 'store'])->name('products.store');
        Route::delete('/products/bulk-delete', [ProductPageController::class, 'bulkDestroy'])->name('products.bulk-delete');
        Route::put('/products/{product}', [ProductPageController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductPageController::class, 'destroy'])->name('products.destroy');

        // Product Codes - Physical Units Management
        Route::get('/product-codes', [ProductCodeController::class, 'index'])->name('product-codes.index');
        Route::post('/product-codes', [ProductCodeController::class, 'store'])->name('product-codes.store');
        Route::post('/product-codes/bulk-status', [ProductCodeController::class, 'bulkUpdateStatus'])->name('product-codes.bulk-update-status');
        Route::delete('/product-codes/bulk-destroy', [ProductCodeController::class, 'bulkDestroy'])->name('product-codes.bulk-destroy');
        Route::put('/product-codes/{productCode}', [ProductCodeController::class, 'update'])->name('product-codes.update');
        Route::delete('/product-codes/{productCode}', [ProductCodeController::class, 'destroy'])->name('product-codes.destroy');

        // Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk-delete');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Roles Management
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::delete('/roles/bulk-delete', [RoleController::class, 'bulkDestroy'])->name('roles.bulk-delete');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');

        // Reports
        Route::get('/reports/all-transactions', [ReportController::class, 'allTransactions'])->name('reports.all-transactions');
        Route::get('/reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
        Route::get('/reports/ticket-sales', [ReportController::class, 'ticketSales'])->name('reports.ticket-sales');
        Route::delete('/reports/bulk-delete', [ReportController::class, 'bulkDelete'])->name('reports.bulk-delete');
        Route::delete('/reports/ticket-sales-bulk-delete', [ReportController::class, 'bulkDeleteTicketSales'])->name('reports.ticket-sales-bulk-delete');
        Route::delete('/reports/bookings-bulk-delete', [ReportController::class, 'bulkDeleteBookings'])->name('reports.bookings-bulk-delete');
        Route::get('/reports/export-all', [ReportController::class, 'exportAll'])->name('reports.export-all');
        Route::get('/reports/export-all-xlsx', [ReportController::class, 'exportAllXlsx'])->name('reports.export-all-xlsx');

        // Audit Logs
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::delete('/audit-logs/bulk-delete', [AuditLogController::class, 'bulkDestroy'])->name('audit-logs.bulk-delete');

        // Attendance / Absensi
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/logs', [AttendanceController::class, 'getLogs'])->name('attendance.logs');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        
        // Doorlock Control (Admin only)
        Route::post('/doorlock/test', [AttendanceController::class, 'testDoorlock'])->name('doorlock.test');
        Route::get('/doorlock/status', [AttendanceController::class, 'doorlockStatus'])->name('doorlock.status');
        Route::get('/doorlock/health', [AttendanceController::class, 'doorlockHealth'])->name('doorlock.health');
    });
});

// Convenience route: redirect /dashboard to /admin/dashboard (auth required)
Route::get('/dashboard', function () {
    return redirect('/admin/dashboard');
})->middleware('auth')->name('dashboard');

// Debug route for quick CSV export during development (only when APP_DEBUG true)
if (config('app.debug')) {
    Route::get('/reports/export-all-debug', [App\Http\Controllers\Admin\ReportController::class, 'exportAll'])->name('reports.export-all-debug');
}

// Temporary test route (unprotected) to allow downloading combined CSV during local testing.
// Remove this route after verification.
Route::get('/reports/export-all-test', [App\Http\Controllers\Admin\ReportController::class, 'exportAll'])->name('reports.export-all-test');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
