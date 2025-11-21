# API Absensi Modern - Documentation

## Base URL
```
http://projectakhir1.serverdata.asia/api
```

## Authentication
Semua request harus menyertakan token keamanan:
```json
{
  "token": "SECURE_KEY_IGASAR"
}
```

---

## Endpoint: POST /api/absen

### Description
Proses absensi karyawan dengan validasi lengkap dan trigger doorlock otomatis.

### Request

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "token": "SECURE_KEY_IGASAR",
  "kode": "0319766798",
  "status": "Masuk"
}
```

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| token | string | Yes | Security token (harus: SECURE_KEY_IGASAR) |
| kode | string | Yes | Kode karyawan |
| status | string | Yes | Status absensi: Masuk, Pulang, Lembur, Pulang Lembur |
| device_code | string | No | Kode device (default: KIOSK-01) |

---

### Response Examples

#### ✅ Success - Absensi Masuk
```json
{
  "status": "success",
  "message": "Absensi Masuk berhasil untuk John Doe",
  "data": {
    "employee_name": "John Doe",
    "employee_code": "0319766798",
    "status": "Masuk",
    "time": "2025-11-21 14:30:45",
    "door_triggered": true
  },
  "timestamp": "2025-11-21 14:30:45"
}
```

#### ❌ Error - Karyawan Tidak Ditemukan
```json
{
  "status": "error",
  "message": "Kode karyawan tidak ditemukan",
  "data": null,
  "timestamp": "2025-11-21 14:30:45"
}
```

#### ⚠️ Error - Validasi Gagal
```json
{
  "status": "error",
  "message": "John Doe sudah absen masuk hari ini.",
  "data": null,
  "timestamp": "2025-11-21 14:30:45"
}
```

#### 🔒 Error - Unauthorized
```json
{
  "status": "error",
  "message": "Unauthorized - Invalid token",
  "data": null,
  "timestamp": "2025-11-21 14:30:45"
}
```

---

## Logika Validasi Absensi

### 1. Status: **Masuk**
- ❌ Tidak boleh absen Masuk 2x dalam sehari
- ✅ Boleh absen Masuk jika belum ada log hari ini

### 2. Status: **Pulang**
- ❌ Harus sudah absen Masuk atau Lembur sebelumnya
- ❌ Tidak boleh Pulang 2x
- ✅ Boleh Pulang setelah Masuk atau Lembur

### 3. Status: **Lembur**
- ❌ Harus sudah absen Pulang sebelumnya
- ✅ Boleh Lembur setelah Pulang

### 4. Status: **Pulang Lembur**
- ❌ Harus sudah absen Lembur sebelumnya
- ✅ Boleh Pulang Lembur setelah Lembur

---

## Doorlock Integration

### Automatic Trigger
Setelah absensi valid, sistem otomatis:
1. ✅ Insert ke `attendance_logs`
2. ✅ Insert ke `door_events`
3. ✅ POST ke Raspberry Pi API:
   ```
   POST http://127.0.0.1:10000/door/open
   {
     "token": "SECURE_KEY_IGASAR",
     "kode": "0319766798",
     "status": "Masuk",
     "delay": 3
   }
   ```

### Door Trigger Failure
Jika Raspberry Pi tidak merespon:
- Absensi tetap tersimpan ✅
- Error dicatat di: `/storage/logs/doorlock_error.log`
- Response: `door_triggered: false`

---

## Database Tables

### `employees`
```sql
id, code, name, is_active, created_at
```

### `attendance_logs`
```sql
id, employee_id, device_code, event_time, status, raw_name
```

### `door_events`
```sql
id, device_code, status, event_time, processed
```

---

## Testing Examples

### cURL Test
```bash
curl -X POST http://projectakhir1.serverdata.asia/api/absen \
  -H "Content-Type: application/json" \
  -d '{
    "token": "SECURE_KEY_IGASAR",
    "kode": "0319766798",
    "status": "Masuk"
  }'
```

### Postman Test
```
POST http://projectakhir1.serverdata.asia/api/absen
Headers:
  Content-Type: application/json
Body (raw JSON):
{
  "token": "SECURE_KEY_IGASAR",
  "kode": "0319766798",
  "status": "Masuk"
}
```

---

## Error Codes

| HTTP Code | Meaning |
|-----------|---------|
| 200 | Success |
| 400 | Bad Request - Data tidak lengkap |
| 403 | Forbidden - Token tidak valid |
| 404 | Not Found - Endpoint tidak ditemukan |
| 405 | Method Not Allowed |
| 500 | Internal Server Error |

---

## Logs Location

- **Attendance Debug**: `/storage/logs/attendance_debug.log`
- **Doorlock Error**: `/storage/logs/doorlock_error.log`

---

## Migration from Old System

Run migration SQL:
```bash
mysql -u walini_user -p walini_pj < database/migrations/migrate_old_attendance.sql
```

This will:
1. Copy `karyawan` → `employees`
2. Copy `log_absensi` → `attendance_logs`
3. Verify migration results
