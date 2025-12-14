# Dokumentasi Login Per Role - AirPanas System

## Panduan Login untuk Setiap Role

Sistem AirPanas memiliki 4 role utama dengan akses terbatas sesuai fungsi pekerjaan mereka. Setiap role hanya dapat mengakses halaman yang sesuai dengan pekerjaannya.

### Akses Halaman Per Role

| Role | Dashboard | Halaman Utama | Admin Panel |
|------|:---------:|:-------------:|:----------:|
| **Ticketing** | ✅ | POS Tiket | ❌ |
| **Booking** | ✅ | Manajemen Villa | ❌ |
| **Parking** | ✅ | Manajemen Parkir | ❌ |
| **Admin** | ✅ | Semua Halaman | ✅ |

---

## 1. Role Ticketing (Petugas Tiket)

**Fungsi**: Mengelola penjualan tiket dan aktivitas yang berkaitan dengan tiket masuk.

### Kredensial Login
```
Email:    ticket@airpanas.local
Username: ticketing
Password: 123123
```

### Akses Fitur
- ✅ Halaman Dashboard
- ✅ **Penjualan Tiket (POS)** - HALAMAN UTAMA
- ❌ Manajemen Produk
- ❌ Manajemen Pengguna
- ❌ Manajemen Role

### Produk yang Terlihat (15 Produk Tiket)
```
1. GOKAR 50 CC
2. ATV 90 CC
3. ATV TEA TOURS
4. FLYING FOX MINI
5. FLYING FOX EXTREME 300M
6. BAJAY TOUR
7. SEPEDA TOUR
8. BOOGIE
9. TIKET MAINAN
10. KERETA API MINI
11. Kolam Renang
12. Kolam Renang Keluarga
13. Kamar Rendam
14. Terapi Ikan
15. Tiket Walini
```

### Pantauan Produk yang Tidak Terlihat
- ❌ Produk Villa (villa bungalow, kerucut, lumbung, panggung)
- ❌ Produk Parkir (RODA 2, RODA 4, RODA 6)

### Menu Akses
1. **Dashboard** - Ringkasan aktivitas hari ini
2. **POS (Point of Sale)** - Buat transaksi penjualan tiket baru
3. **Laporan** - Lihat laporan penjualan

Jika mencoba akses halaman lain (Booking, Parkir, Admin) → akan diarahkan kembali ke POS Tiket

---

## 2. Role Booking (Petugas Villa/Akomodasi)

**Fungsi**: Mengelola reservasi villa dan akomodasi tamu.

### Kredensial Login
```
Email:    booking@airpanas.local
Username: booking
Password: 123123
```

### Akses Fitur
- ✅ Halaman Dashboard
- ✅ **Manajemen Reservasi (Bookings)** - HALAMAN UTAMA
- ❌ Penjualan Tiket
- ❌ Manajemen Produk
- ❌ Manajemen Pengguna

### Produk yang Terlihat (8 Produk Villa)
```
Tipe Villa: Bungalow
1. villa bungalow - weekday
2. villa bungalow - weekend

Tipe Villa: Kerucut
3. villa kerucut - weekday
4. villa kerucut - weekend

Tipe Villa: Lumbung
5. villa lumbung - weekday
6. villa lumbung - weekend

Tipe Villa: Panggung
7. villa panggung - weekday
8. villa panggung - weekend
```

### Pantauan Produk yang Tidak Terlihat
- ❌ Produk Tiket (GOKAR, ATV, FLYING FOX, dll)
- ❌ Produk Parkir (RODA 2, RODA 4, RODA 6)

### Menu Akses
1. **Dashboard** - Ringkasan reservasi
2. **Reservasi** - Kelola pemesanan villa
3. **Laporan** - Lihat laporan reservasi

Jika mencoba akses halaman lain (Tiket, Parkir, Admin) → akan diarahkan kembali ke Reservasi

---

## 3. Role Parking (Petugas Parkir)

**Fungsi**: Mengelola transaksi parkir dan kendaraan yang masuk.

### Kredensial Login
```
Email:    parking@airpanas.local
Username: parking
Password: 123123
```

### Akses Fitur
- ✅ Halaman Dashboard
- ✅ **Kelola Parkir (Parking Management)** - HALAMAN UTAMA
- ✅ Monitoring Parkir
- ❌ Penjualan Tiket
- ❌ Manajemen Villa
- ❌ Manajemen Produk

### Produk yang Terlihat (3 Produk Parkir)
```
1. PARKIR RODA 2    (Sepeda Motor)
2. PARKIR RODA 4    (Mobil)
3. PARKIR RODA 6    (Bus/Truk Besar)
```

### Pantauan Produk yang Tidak Terlihat
- ❌ Produk Tiket (GOKAR, ATV, FLYING FOX, dll)
- ❌ Produk Villa (villa bungalow, kerucut, lumbung, panggung)

### Menu Akses
1. **Dashboard** - Ringkasan parkir hari ini
2. **Parkir** - Buat transaksi parkir baru
3. **Monitor** - Monitor kendaraan yang parkir

Jika mencoba akses halaman lain (Tiket, Villa, Admin) → akan diarahkan kembali ke Manajemen Parkir

---

## 4. Role Admin (Administrator)

**Fungsi**: Mengelola seluruh sistem, pengguna, dan konfigurasi.

### Kredensial Login
```
Email:    admin@airpanas.local
Username: admin
Password: (sesuai dengan password yang Anda set)
```

### Akses Fitur
- ✅ Halaman Dashboard
- ✅ **Manajemen Produk** - Lihat SEMUA 26 produk
- ✅ Manajemen Pengguna
- ✅ Manajemen Role & Permissions
- ✅ Manajemen Kode Produk
- ✅ Manajemen Kategori
- ✅ Audit Log
- ✅ Laporan Lengkap

### Produk yang Terlihat (26 Produk - SEMUA)
```
TIKET (15):
- GOKAR 50 CC
- ATV 90 CC
- ATV TEA TOURS
- FLYING FOX MINI
- FLYING FOX EXTREME 300M
- BAJAY TOUR
- SEPEDA TOUR
- BOOGIE
- TIKET MAINAN
- KERETA API MINI
- Kolam Renang
- Kolam Renang Keluarga
- Kamar Rendam
- Terapi Ikan
- Tiket Walini

VILLA (8):
- villa bungalow - weekday
- villa bungalow - weekend
- villa kerucut - weekday
- villa kerucut - weekend
- villa lumbung - weekday
- villa lumbung - weekend
- villa panggung - weekday
- villa panggung - weekend

PARKIR (3):
- PARKIR RODA 2
- PARKIR RODA 4
- PARKIR RODA 6
```

### Menu Akses
1. **Dashboard** - Ringkasan keseluruhan sistem
2. **Produk** - Kelola semua produk
3. **Pengguna** - Kelola pengguna dan role
4. **Kode Produk** - Kelola unit/kode tiket
5. **Audit Log** - Lihat riwayat aktivitas

---

## Tabel Perbandingan Akses Per Role

| Fitur | Ticketing | Booking | Parking | Admin |
|-------|:---------:|:-------:|:-------:|:-----:|
| **Dashboard** | ✅ | ✅ | ✅ | ✅ |
| **Produk Tiket** | ✅ | ❌ | ❌ | ✅ |
| **Produk Villa** | ❌ | ✅ | ❌ | ✅ |
| **Produk Parkir** | ❌ | ❌ | ✅ | ✅ |
| **POS/Penjualan Tiket** | ✅ | ❌ | ❌ | ✅ |
| **Manajemen Reservasi** | ❌ | ✅ | ❌ | ✅ |
| **Manajemen Parkir** | ❌ | ❌ | ✅ | ✅ |
| **Monitor Parkir** | ❌ | ❌ | ✅ | ✅ |
| **Manajemen Pengguna** | ❌ | ❌ | ❌ | ✅ |
| **Manajemen Role** | ❌ | ❌ | ❌ | ✅ |
| **Audit Log** | ❌ | ❌ | ❌ | ✅ |
| **Laporan** | ✅ | ✅ | ✅ | ✅ |

---

## Panduan Testing Role-Based Access

### Step 1: Login dengan Role Ticketing
1. Buka halaman login
2. Masukkan:
   - Email: `ticket@airpanas.local`
   - Password: `123123`
3. Klik "Login"
4. Navigasi ke menu **Produk**
5. **Verifikasi**: Hanya 15 produk tiket yang terlihat

### Step 2: Login dengan Role Booking
1. Logout dari akun sebelumnya
2. Login dengan:
   - Email: `booking@airpanas.local`
   - Password: `123123`
3. Navigasi ke menu **Produk** (atau **Reservasi**)
4. **Verifikasi**: Hanya 8 produk villa yang terlihat

### Step 3: Login dengan Role Parking
1. Logout dari akun sebelumnya
2. Login dengan:
   - Email: `parking@airpanas.local`
   - Password: `123123`
3. Navigasi ke menu **Produk** atau **Parkir**
4. **Verifikasi**: Hanya 3 produk parkir yang terlihat

### Step 4: Login dengan Role Admin
1. Logout dari akun sebelumnya
2. Login dengan akun admin Anda
3. Navigasi ke menu **Produk**
4. **Verifikasi**: Semua 26 produk terlihat

---

## Fitur Role-Based Filtering

### Penerapan Filtering
Filtering produk berdasarkan role diterapkan pada:

1. **Halaman Produk** (`/admin/products`)
   - Menampilkan daftar produk sesuai role

2. **Form Penjualan Tiket** (`/admin/ticket-sales/create`)
   - Dropdown produk hanya menampilkan tiket untuk ticketing staff

3. **Form Reservasi Villa** (`/admin/bookings/create`)
   - Dropdown produk hanya menampilkan villa untuk booking staff

4. **Form Transaksi Parkir** (`/admin/parking/create`)
   - Menampilkan tipe kendaraan dan harga parkir untuk parking staff

### Backend Implementation
Filtering diimplementasikan di server-side (backend) sehingga:
- ✅ Aman - tidak bisa di-bypass dari client-side
- ✅ Konsisten - filtering sama di semua halaman
- ✅ Efisien - hanya data yang diizinkan yang dikirim ke frontend

---

## Troubleshooting

### Jika login gagal:
1. Pastikan email/username benar
2. Pastikan password adalah `123123` (untuk test users)
3. Pastikan user aktif di database
4. Cek di menu Admin > Pengguna apakah user sudah ada

### Jika produk tidak tampil:
1. Pastikan sudah login dengan role yang tepat
2. Reload halaman (Ctrl+F5 atau Cmd+Shift+R)
3. Periksa di Admin > Produk untuk melihat semua produk

### Jika ada error:
1. Cek console browser (F12 > Console)
2. Cek server logs: `tail -f storage/logs/laravel.log`

---

## Catatan Penting

- **Password Test Users**: Semua test user menggunakan password `123123`
- **Role-Based Access**: Diterapkan di database level untuk keamanan maksimal
- **Admin Access**: Admin/Superadmin dapat melihat SEMUA produk dari semua kategori
- **Scalability**: Sistem dapat dengan mudah diextend dengan menambah role baru di database

---

## Quick Reference Login Commands

Untuk testing cepat, gunakan credentials berikut:

```bash
# Ticketing Staff
Email: ticket@airpanas.local
Pass:  123123

# Booking Staff
Email: booking@airpanas.local
Pass:  123123

# Parking Staff
Email: parking@airpanas.local
Pass:  123123

# Admin
Email: admin@airpanas.local
Pass:  (your admin password)
```

---

**Versi Dokumentasi**: 1.0  
**Tanggal Update**: 13 Desember 2025  
**Status**: Aktif dan Teruji
