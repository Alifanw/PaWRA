# Role-Based Access Control - Testing Guide

## Overview

Sistem AirPanas sekarang telah diimplementasikan **Role-Based Access Control (RBAC)** yang ketat:
- Setiap role hanya dapat mengakses **1 halaman utama** + Dashboard
- Jika mencoba akses halaman lain → otomatis redirect ke halaman yang sesuai role mereka

## Implementasi Teknis

### Route Protection dengan Middleware
```php
// Ticketing role - hanya bisa akses ticket-sales
Route::middleware('role:ticketing,admin,superadmin')->group(function () {
    Route::get('/ticket-sales', ...);
    Route::post('/ticket-sales', ...);
    // ... other ticket-sales routes
});

// Booking role - hanya bisa akses bookings
Route::middleware('role:booking,admin,superadmin')->group(function () {
    Route::get('/bookings', ...);
    Route::post('/bookings', ...);
    // ... other bookings routes
});

// Parking role - hanya bisa akses parking
Route::middleware('role:parking,admin,superadmin')->group(function () {
    Route::get('/parking', ...);
    Route::post('/parking', ...);
    // ... other parking routes
});

// Admin only
Route::middleware('role:admin,superadmin')->group(function () {
    // Products, Users, Roles, Reports, Audit Logs
});
```

### Middleware Logic
```php
// RestrictByRole middleware mengecek:
1. Apakah user sudah login?
2. Apakah user memiliki role yang diizinkan untuk route ini?
3. Jika tidak → redirect ke halaman sesuai role mereka
```

---

## Test Scenario - Ticketing User

### Skenario 1: Akses POS Tiket (ALLOWED)
```
1. Login: ticket@airpanas.local / 123123
2. Navigate to: /admin/ticket-sales
3. Expected: Halaman POS Tiket terbuka ✅
```

### Skenario 2: Coba Akses Booking (BLOCKED)
```
1. User masih login sebagai ticketing
2. Navigate to: /admin/bookings
3. Expected: Redirect otomatis ke /admin/ticket-sales ✅
```

### Skenario 3: Coba Akses Produk (BLOCKED)
```
1. User masih login sebagai ticketing
2. Navigate to: /admin/products
3. Expected: Redirect otomatis ke /admin/ticket-sales ✅
```

### Skenario 4: Dashboard (ALLOWED)
```
1. User masih login sebagai ticketing
2. Navigate to: /admin/dashboard
3. Expected: Halaman Dashboard terbuka ✅
```

---

## Test Scenario - Booking User

### Skenario 1: Akses Booking (ALLOWED)
```
1. Logout dari ticketing user
2. Login: booking@airpanas.local / 123123
3. Navigate to: /admin/bookings
4. Expected: Halaman Booking terbuka ✅
```

### Skenario 2: Coba Akses POS Tiket (BLOCKED)
```
1. User masih login sebagai booking
2. Navigate to: /admin/ticket-sales
3. Expected: Redirect otomatis ke /admin/bookings ✅
```

### Skenario 3: Coba Akses Parkir (BLOCKED)
```
1. User masih login sebagai booking
2. Navigate to: /admin/parking
3. Expected: Redirect otomatis ke /admin/bookings ✅
```

---

## Test Scenario - Parking User

### Skenario 1: Akses Parking (ALLOWED)
```
1. Logout dari booking user
2. Login: parking@airpanas.local / 123123
3. Navigate to: /admin/parking
4. Expected: Halaman Parking terbuka ✅
```

### Skenario 2: Coba Akses POS Tiket (BLOCKED)
```
1. User masih login sebagai parking
2. Navigate to: /admin/ticket-sales
3. Expected: Redirect otomatis ke /admin/parking ✅
```

### Skenario 3: Coba Akses Booking (BLOCKED)
```
1. User masih login sebagai parking
2. Navigate to: /admin/bookings
3. Expected: Redirect otomatis ke /admin/parking ✅
```

---

## Test Scenario - Admin User

### Skenario 1: Akses Semua Halaman (ALLOWED)
```
1. Logout dari parking user
2. Login: admin@airpanas.local / (your admin password)
3. Navigate to: /admin/products
4. Expected: Halaman Produk terbuka ✅

5. Navigate to: /admin/ticket-sales
6. Expected: Halaman POS Tiket terbuka ✅

7. Navigate to: /admin/bookings
8. Expected: Halaman Booking terbuka ✅

9. Navigate to: /admin/parking
10. Expected: Halaman Parking terbuka ✅

11. Navigate to: /admin/users
12. Expected: Halaman Manajemen Pengguna terbuka ✅
```

---

## Error Handling - Error 419 (CSRF Token Expired)

Jika mendapat error 419 saat login:

### Solusi 1: Clear Browser Cache
```
1. Ctrl+Shift+Delete (atau Cmd+Shift+Delete on Mac)
2. Pilih: Cookies and other site data
3. Pilih: All time
4. Click: Clear data
5. Refresh halaman login dan coba lagi
```

### Solusi 2: Incognito Mode
```
1. Buka halaman login di Incognito Mode
2. Coba login lagi dengan email: ticket@airpanas.local
```

### Solusi 3: Database Session Cleanup
```bash
cd /var/www/airpanas
php artisan tinker
>>> \DB::table('sessions')->delete()
>>> exit
```

### Solusi 4: Check Server Logs
```bash
tail -f /var/www/airpanas/storage/logs/laravel.log
```

---

## Frontend Navigation Menu Update

Setelah role-based access diimplementasikan, sidebar menu akan otomatis:
- **Ticketing**: Hanya menampilkan "POS Tiket" + Dashboard + Logout
- **Booking**: Hanya menampilkan "Booking" + Dashboard + Logout
- **Parking**: Hanya menampilkan "Parkir" + Dashboard + Logout
- **Admin**: Menampilkan semua menu: Produk, Pengguna, Role, Reports, dll

Menu items yang tidak diizinkan akan **disembunyikan dari sidebar**.

---

## Quick Test Commands

### Test Login & Role
```bash
cd /var/www/airpanas
php artisan tinker

# Verify ticketing user
$user = \App\Models\User::where('email', 'ticket@airpanas.local')->first();
$user->roles->pluck('name')->implode(', ');  // Output: ticketing

# Verify booking user
$user = \App\Models\User::where('email', 'booking@airpanas.local')->first();
$user->roles->pluck('name')->implode(', ');  // Output: booking

# Verify parking user
$user = \App\Models\User::where('email', 'parking@airpanas.local')->first();
$user->roles->pluck('name')->implode(', ');  // Output: parking
```

### Test Middleware Logic
```bash
# Simulate role check untuk ticketing user
$user = \App\Models\User::where('email', 'ticket@airpanas.local')->first();
$userRoles = $user->roles()->pluck('name')->toArray();

// Cek apakah user bisa akses ticketing routes
in_array('ticketing', $userRoles);  // Output: true
in_array('booking', $userRoles);    // Output: false
```

---

## Files Modified

1. **Created**: `/app/Http/Middleware/RestrictByRole.php`
   - Middleware untuk enforce role-based access

2. **Modified**: `/app/Http/Kernel.php`
   - Registered RestrictByRole middleware

3. **Modified**: `/routes/web.php`
   - Updated route groups dengan middleware('role:...')
   - Separated routes by role requirement

4. **Modified**: `/DOKUMENTASI_LOGIN_PER_ROLE.md`
   - Updated documentation dengan access restrictions

---

## Expected Behavior Summary

| User Role | Can Access | Cannot Access | Auto-Redirect To |
|-----------|-----------|----------------|-----------------|
| **Ticketing** | POS Tiket, Dashboard | Booking, Parkir, Produk, Users | /admin/ticket-sales |
| **Booking** | Booking, Dashboard | POS Tiket, Parkir, Produk, Users | /admin/bookings |
| **Parking** | Parkir, Dashboard | POS Tiket, Booking, Produk, Users | /admin/parking |
| **Admin** | Semua | Tidak ada | - |

---

## Troubleshooting Checklist

- [ ] Database sessions tabel ada?
  ```bash
  php artisan tinker
  >>> \Schema::hasTable('sessions')
  ```

- [ ] Test users ada?
  ```bash
  >>> \App\Models\User::where('email', 'ticket@airpanas.local')->exists()
  ```

- [ ] Test users punya role?
  ```bash
  >>> \App\Models\User::where('email', 'ticket@airpanas.local')->first()->roles->count()
  ```

- [ ] Middleware registered?
  ```bash
  grep -r "RestrictByRole" app/Http/Kernel.php
  ```

- [ ] Routes updated?
  ```bash
  php artisan route:list | grep "ticket-sales"
  ```

---

## Notes

- Semua role-based check dilakukan **server-side** untuk keamanan maksimal
- User tidak bisa bypass access control dari client-side
- Error 419 hanya terjadi jika session expired (lihat solusi di atas)
- Admin/Superadmin bypass semua restrictions

---

**Last Updated**: 13 Desember 2025  
**Status**: Ready for Testing
