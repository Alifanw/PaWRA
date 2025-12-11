<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Booking;
use App\Models\TicketSale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get today's stats
        $today = now()->format('Y-m-d');
        
        $stats = [
            'today_bookings' => Booking::whereDate('created_at', $today)->count(),
            'today_revenue' => TicketSale::whereDate('sale_date', $today)->sum('net_amount'),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'total_products' => Product::where('is_active', true)->count(),
        ];

        // Recent bookings
        $recentBookings = Booking::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->customer_name,
                    'checkin' => $booking->checkin,
                    'checkout' => $booking->checkout,
                    'total_amount' => $booking->total_amount,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at->format('Y-m-d H:i'),
                ];
            });

        // Weekly revenue chart data
        $weeklyRevenue = TicketSale::select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(net_amount) as revenue')
            )
            ->where('sale_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'revenue' => $item->revenue,
                ];
            });

        $user = auth()->user();

        $roles = $user->roles()->get()->map(function ($r) {
            return ['id' => $r->id, 'name' => $r->name, 'display_name' => $r->display_name ?? null];
        })->values();

        $permissions = $user->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn($role) => $role->permissions->pluck('permission'))
            ->unique()
            ->values()
            ->toArray();

        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => $stats,
            'recentBookings' => $recentBookings,
            'dailySales' => $weeklyRevenue,
            'auth' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ],
            ],
        ]);
    }
}
