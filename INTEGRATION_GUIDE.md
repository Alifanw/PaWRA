# PANDUAN INTEGRASI SISTEM ABSENSI

## 📊 Data Karyawan Terdaftar

Berikut karyawan yang sudah berhasil diimport ke sistem:

| ID | Kode | Nama | Status |
|----|------|------|--------|
| 4 | 0319766798 | Adeli | Aktif ✅ |
| 5 | 0084807438 | Rasid | Aktif ✅ |
| 6 | 0151920398 | Isan | Aktif ✅ |
| 7 | 0909479960 | Alip | Aktif ✅ |

## 🔄 Migrasi Data dari Sistem Lama

### Database Lama (Raspberry Pi)
- **Database:** `walini_db`
- **Tabel:** `karyawan` 
- **Struktur:** `ids`, `kodes`, `nama`
- **Host:** `website.airpanaswalini.com`

### Database Baru (Server Utama)
- **Database:** `walini_pj`
- **Tabel:** `employees`
- **Struktur:** `id`, `code`, `name`, `is_active`, `created_at`, `updated_at`
- **Host:** `localhost`

## 🧪 Testing dengan Karyawan Asli

### Test Absen Masuk
```bash
cd /var/www/airpanas/api
./quick_test.sh 0319766798 masuk
```

**Expected Result:**
```json
{
    "status": "success",
    "message": "Absensi masuk berhasil",
    "data": {
        "nama": "Adeli",
        "waktu": "2025-11-21 06:17:30",
        "door_triggered": true/false
    }
}
```

### Test Absen Pulang
```bash
./quick_test.sh 0319766798 pulang
```

### Test dengan Karyawan Lain
```bash
./quick_test.sh 0084807438 masuk  # Rasid
./quick_test.sh 0151920398 masuk  # Isan
./quick_test.sh 0909479960 masuk  # Alip
```

## 🚪 Konfigurasi Doorlock

### Current Config (`/api/config/Config.php`)
```php
const DOORLOCK_API_URL = 'http://192.168.30.108:5000/door/open';
const DOORLOCK_TOKEN = 'SECURE_KEY_IGASAR';
const DOORLOCK_DEFAULT_DELAY = 3;
```

### Network Troubleshooting

**Jika doorlock timeout:**

1. **Cek koneksi ke Raspberry Pi:**
   ```bash
   ping 192.168.30.108
   ```

2. **Cek Flask service di Pi:**
   ```bash
   curl http://192.168.30.108:5000/
   ```

3. **Test door status:**
   ```bash
   curl -X GET http://192.168.30.108:5000/door/status
   ```

4. **Test manual trigger:**
   ```bash
   curl -X POST http://192.168.30.108:5000/door/open \
     -H "Content-Type: application/json" \
     -d '{"token":"SECURE_KEY_IGASAR","delay":3}'
   ```

### Alternatif IP untuk Testing

Jika `192.168.30.108` tidak bisa diakses dari server, coba:

```php
// Jika Pi dan server di host yang sama
const DOORLOCK_API_URL = 'http://127.0.0.1:5000/door/open';

// Jika menggunakan domain
const DOORLOCK_API_URL = 'http://website.airpanaswalini.com:5000/door/open';
```

## 📝 Catatan Penting

### ⚠️ Doorlock Error Handling
Sistem dirancang **tetap menyimpan absensi** meskipun doorlock gagal:
- ✅ Absensi tersimpan ke database
- ✅ Error dicatat di `doorlock.log`
- ✅ Response tetap success
- ⚠️ `door_triggered: false` jika gagal

### 🔒 Behavior yang Benar
```
Absensi Berhasil + Doorlock Gagal = TETAP SUCCESS
(Absensi lebih penting daripada doorlock)
```

## 🔧 Troubleshooting

### Problem: "Karyawan tidak ditemukan"
**Solution:** Import data karyawan
```bash
mysql -u walini_user -p'raHAS1@walini' walini_pj << EOF
INSERT INTO employees (code, name, is_active, created_at, updated_at) VALUES
('KODE_BARU', 'NAMA_KARYAWAN', 1, NOW(), NOW());
EOF
```

### Problem: "Terlalu cepat. Tunggu X detik"
**Cause:** Rate limiting (10 detik)
**Solution:** 
- Tunggu 10 detik
- Atau clear attendance logs: `DELETE FROM attendance_logs WHERE employee_id=X;`

### Problem: Doorlock timeout/connection refused
**Solutions:**
1. Pastikan Flask running di Pi: `ps aux | grep python`
2. Cek firewall: `sudo ufw status`
3. Test port: `nc -zv 192.168.30.108 5000`
4. Update IP di `Config.php` jika berubah

### Problem: Validasi flow gagal
**Example:** "Harus absen masuk dulu"
**Solution:** Ikuti urutan yang benar:
1. masuk
2. pulang (setelah masuk)
3. lembur (setelah pulang)
4. pulang_lembur (setelah lembur)

## 📊 Monitoring

### Cek Attendance Logs
```bash
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "
SELECT 
    e.code,
    e.name,
    a.status,
    DATE_FORMAT(a.event_time, '%H:%i:%s') as time
FROM attendance_logs a
JOIN employees e ON a.employee_id = e.id
WHERE DATE(a.event_time) = CURDATE()
ORDER BY a.event_time DESC;
"
```

### Cek Door Events
```bash
mysql -u walini_user -p'raHAS1@walini' walini_pj -e "
SELECT 
    employee_code,
    status,
    http_code,
    DATE_FORMAT(event_time, '%H:%i:%s') as time,
    CASE 
        WHEN http_code = 200 THEN '✅ Success'
        WHEN http_code IS NULL THEN '⏳ Pending'
        ELSE '❌ Failed'
    END as result
FROM door_events 
WHERE DATE(event_time) = CURDATE()
ORDER BY event_time DESC;
"
```

### Cek Logs
```bash
# Attendance log
tail -f /var/www/airpanas/api/logs/absen.log

# Doorlock log
tail -f /var/www/airpanas/api/logs/doorlock.log

# Filter success only
grep "successfully" /var/www/airpanas/api/logs/doorlock.log

# Filter errors only
grep "ERROR" /var/www/airpanas/api/logs/doorlock.log
```

## 🎯 Production Checklist

- [x] Import 4 karyawan dari sistem lama
- [x] Test absensi dengan kode asli (0319766798)
- [x] Verifikasi data tersimpan ke database
- [ ] Test koneksi ke Raspberry Pi doorlock
- [ ] Update IP doorlock jika diperlukan
- [ ] Test complete flow: masuk→pulang→lembur→pulang_lembur
- [ ] Setup monitoring logs
- [ ] Backup database sebelum go-live
- [ ] Dokumentasi untuk user

## 📞 Support

Jika ada masalah:
1. Cek logs di `/var/www/airpanas/api/logs/`
2. Test component: `php test_components.php`
3. Test endpoint: `php test_endpoint.php`
4. Cek database connection
5. Verify Raspberry Pi Flask service running
