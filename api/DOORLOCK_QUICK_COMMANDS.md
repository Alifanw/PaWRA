# Quick Commands - Doorlock Testing

## 🧪 TEST MOCK SERVER (Localhost)

### Start Mock Server:
```bash
cd /var/www/airpanas/api
php -S localhost:5000 mock_doorlock_server.php > /tmp/doorlock_mock.log 2>&1 &
echo $! > /tmp/doorlock_mock.pid
```

### Test Mock:
```bash
curl -X POST http://localhost:5000/door/open \
  -H "Authorization: Bearer SECURE_KEY_IGASAR" \
  -H "Content-Type: application/json" \
  -d '{"delay": 3}'
```

### Stop Mock Server:
```bash
kill $(cat /tmp/doorlock_mock.pid)
```

### Update Config for Mock:
```php
// In api/config/Config.php
const DOORLOCK_API_URL = 'http://localhost:5000/door/open';
```

---

## 🔌 TEST REAL RASPBERRY PI

### Check Network:
```bash
ping -c 3 192.168.30.108
```

### Check Server Running:
```bash
curl http://192.168.30.108:5000/health
```

### Test Trigger:
```bash
curl -X POST http://192.168.30.108:5000/door/open \
  -H "Authorization: Bearer SECURE_KEY_IGASAR" \
  -H "Content-Type: application/json" \
  -d '{"delay": 3}'
```

### Update Config for Real Pi:
```php
// In api/config/Config.php
const DOORLOCK_API_URL = 'http://192.168.30.108:5000/door/open';
```

---

## 📱 TEST VIA ATTENDANCE API

### Test Attendance (Triggers Doorlock):
```bash
cd /var/www/airpanas/api
./quick_test.sh 0319766798 masuk
```

### Check Response:
Look for: `"door_triggered": true`

### View Logs:
```bash
# Attendance log
tail -f logs/doorlock.log

# Last 5 entries
tail -5 logs/doorlock.log
```

---

## 🔍 DEBUGGING

### Check Door Events in Database:
```bash
mysql -u walini_user -p'raHAS1@walini' walini_pj -e \
  "SELECT id, employee_code, status, http_code, 
   LEFT(response_message, 50) as response, event_time 
   FROM door_events 
   ORDER BY event_time DESC LIMIT 5;"
```

### Expected HTTP Codes:
- **200** = Success (door triggered)
- **401** = Invalid token
- **0** = Timeout/network error

### Check Last Trigger:
```bash
mysql -u walini_user -p'raHAS1@walini' walini_pj -e \
  "SELECT employee_code, status, http_code, event_time 
   FROM door_events 
   WHERE http_code = 200 
   ORDER BY event_time DESC LIMIT 1;"
```

---

## 🚀 RASPBERRY PI SETUP (SSH)

### Copy Files to Pi:
```bash
scp doorlock_server_real.py pi@192.168.30.108:/home/pi/
```

### SSH to Raspberry Pi:
```bash
ssh pi@192.168.30.108
```

### Start Server on Pi:
```bash
sudo python3 /home/pi/doorlock_server_real.py
```

### Or as Service:
```bash
sudo systemctl start doorlock
sudo systemctl status doorlock
journalctl -u doorlock -f
```

---

## 📊 MONITORING

### Real-time Door Events:
```bash
watch -n 1 "mysql -u walini_user -p'raHAS1@walini' walini_pj -e 'SELECT COUNT(*) as total_triggers FROM door_events WHERE http_code = 200'"
```

### Today's Statistics:
```bash
mysql -u walini_user -p'raHAS1@walini' walini_pj -e \
  "SELECT 
    COUNT(*) as total_attempts,
    SUM(CASE WHEN http_code = 200 THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN http_code = 0 THEN 1 ELSE 0 END) as timeouts,
    SUM(CASE WHEN http_code = 401 THEN 1 ELSE 0 END) as auth_failed
   FROM door_events 
   WHERE DATE(event_time) = CURDATE();"
```

---

## 🎯 INTEGRATION TEST FLOW

### 1. Start Mock (Development):
```bash
cd /var/www/airpanas/api
php -S localhost:5000 mock_doorlock_server.php &
```

### 2. Update Config:
Change `DOORLOCK_API_URL` to `localhost:5000`

### 3. Test Attendance:
```bash
./quick_test.sh TEST123 masuk
```

### 4. Verify Response:
Should see: `"door_triggered": true`

### 5. Check Logs:
```bash
tail -1 logs/doorlock.log
```

### 6. Switch to Production:
Change `DOORLOCK_API_URL` back to `192.168.30.108:5000`

---

## ⚡ QUICK FIXES

### Problem: Timeout Error
```bash
# Check network
ping 192.168.30.108

# Check server
curl http://192.168.30.108:5000/health

# Restart server on Pi
ssh pi@192.168.30.108 "sudo systemctl restart doorlock"
```

### Problem: 401 Auth Error
```bash
# Verify token matches in both:
grep DOORLOCK_TOKEN api/config/Config.php
ssh pi@192.168.30.108 "grep VALID_TOKEN /home/pi/doorlock_server_real.py"
```

### Problem: Door Triggered but Not Opening
```bash
# SSH to Pi and check GPIO
ssh pi@192.168.30.108

# Test GPIO manually
sudo python3 << EOF
import RPi.GPIO as GPIO
import time
GPIO.setmode(GPIO.BCM)
GPIO.setup(17, GPIO.OUT)
GPIO.output(17, GPIO.HIGH)
time.sleep(2)
GPIO.output(17, GPIO.LOW)
GPIO.cleanup()
EOF
```

---

## 📝 CHEAT SHEET

| Action | Command |
|--------|---------|
| Start mock | `php -S localhost:5000 mock_doorlock_server.php &` |
| Stop mock | `kill $(cat /tmp/doorlock_mock.pid)` |
| Test attendance | `./quick_test.sh <kode> masuk` |
| View logs | `tail -f logs/doorlock.log` |
| Check DB | `mysql ... "SELECT * FROM door_events ORDER BY id DESC LIMIT 5"` |
| Ping Pi | `ping 192.168.30.108` |
| SSH to Pi | `ssh pi@192.168.30.108` |
| Pi service status | `sudo systemctl status doorlock` |
| Pi logs | `journalctl -u doorlock -f` |

---

*Quick Reference v1.0*
