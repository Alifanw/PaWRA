# �� RASPBERRY PI DOORLOCK - QUICK START GUIDE

## 📍 LANGKAH PERTAMA: CARI IP RASPBERRY PI

Raspberry Pi tidak terdeteksi di network 192.168.193.x atau 192.168.30.x

### Cara 1: Gunakan Monitor + Keyboard (PALING MUDAH)

1. **Hubungkan ke Raspberry Pi:**
   - Monitor via HDMI
   - Keyboard USB
   - Power on Raspberry Pi

2. **Login:**
   ```
   Login: pi
   Password: raspberry (atau password yang sudah diset)
   ```

3. **Cek IP Address:**
   ```bash
   hostname -I
   ```
   
   Output contoh: `192.168.1.100` ← IP ini yang dicatat!

4. **Jika belum ada IP (offline):**
   ```bash
   # Cek network interface
   ip addr show
   
   # Jika ethernet:
   sudo dhclient eth0
   
   # Jika WiFi, configure:
   sudo raspi-config
   # Pilih: System Options → Wireless LAN
   ```

### Cara 2: Via Router Admin Panel

1. Buka browser, akses router:
   - Common addresses: `192.168.1.1`, `192.168.0.1`, `192.168.193.1`
   - Login router (cek label di router)

2. Cari menu:
   - "Connected Devices" / "DHCP Client List" / "Device List"

3. Cari device:
   - Name: "raspberrypi" / "pi" 
   - MAC prefix: B8:27:EB, DC:A6:32, E4:5F:01 (Raspberry Pi Foundation)

### Cara 3: Scan dengan nmap (perlu install dulu)

```bash
# Install nmap
sudo apt install nmap

# Scan network
sudo nmap -sn 192.168.193.0/24

# Atau scan multiple networks
for net in 192.168.{0,1,30,193}; do
    echo "Scanning $net.0/24..."
    sudo nmap -sn $net.0/24 | grep -B 2 Raspberry
done
```

---

## 🔧 SETELAH DAPAT IP RASPBERRY PI

### Langkah 1: Test Koneksi

```bash
# Ganti <IP_PI> dengan IP yang didapat
ping -c 3 <IP_PI>
```

Contoh: `ping -c 3 192.168.1.100`

**Jika success:** Lanjut ke Langkah 2  
**Jika timeout:** Pi masih offline, cek power/network

### Langkah 2: SSH ke Raspberry Pi

```bash
ssh pi@<IP_PI>
# Password default: raspberry
```

**Jika "Connection refused":**
```bash
# Dari Pi langsung (pakai monitor):
sudo raspi-config
# Interface Options → SSH → Enable
sudo systemctl enable ssh
sudo systemctl start ssh
```

### Langkah 3: Install Dependencies

```bash
# Di Raspberry Pi (via SSH):

# Update system
sudo apt update
sudo apt upgrade -y

# Install Python packages
sudo apt install python3-flask python3-rpi.gpio -y

# Verify
python3 << 'PYEOF'
import flask
import RPi.GPIO
print("✅ All packages installed!")
PYEOF
```

### Langkah 4: Copy Server File

```bash
# Di server attendance (komputer ini):
cd /var/www/airpanas/api

# Copy ke Raspberry Pi
scp doorlock_server_real.py pi@<IP_PI>:/home/pi/

# Verify
ssh pi@<IP_PI> "ls -lh /home/pi/doorlock_server_real.py"
```

### Langkah 5: Test Relay Wiring

```bash
# SSH ke Pi
ssh pi@<IP_PI>

# Test GPIO manual
sudo python3 << 'PYEOF'
import RPi.GPIO as GPIO
import time

print("🔧 Testing GPIO17 relay...")
GPIO.setmode(GPIO.BCM)
GPIO.setup(17, GPIO.OUT)

print("→ Relay ON (should CLICK)")
GPIO.output(17, GPIO.HIGH)
time.sleep(2)

print("→ Relay OFF")
GPIO.output(17, GPIO.LOW)

GPIO.cleanup()
print("✅ Test complete!")
PYEOF
```

**Expected:**
- Relay bunyi "CLICK" 
- LED pada relay menyala/mati
- Jika door lock terhubung, pintu membuka/tutup

**Jika tidak ada CLICK:**
- Check wiring: Pin 2 (5V) → VCC, Pin 6 (GND) → GND, Pin 11 → IN
- Check LED relay (harus menyala jika dapat power)
- Try GPIO lain: ubah `17` ke `27` atau `22`

### Langkah 6: Start Server

```bash
# Di Raspberry Pi
sudo python3 /home/pi/doorlock_server_real.py
```

**Expected Output:**
```
==================================================
RASPBERRY PI DOORLOCK SERVER - REAL HARDWARE
==================================================
GPIO Mode: REAL HARDWARE
GPIO Pin: 17 (BCM)
Token: SECURE_KEY_IGASAR
Default Delay: 3 seconds
--------------------------------------------------
 * Running on http://0.0.0.0:5000
```

**Biarkan terminal ini terbuka!**

### Langkah 7: Test dari Server Attendance

```bash
# Di server attendance (terminal baru):

# 1. Health check
curl http://<IP_PI>:5000/health

# Expected: {"status":"healthy","gpio_mode":"REAL"}

# 2. Test relay (quick pulse)
curl -X POST http://<IP_PI>:5000/test \
  -H "Authorization: Bearer SECURE_KEY_IGASAR"

# Expected: Relay CLICK + {"status":"success"}

# 3. Test door open (full 3 seconds)
curl -X POST http://<IP_PI>:5000/door/open \
  -H "Authorization: Bearer SECURE_KEY_IGASAR" \
  -H "Content-Type: application/json" \
  -d '{"delay": 3}'

# Expected: Pintu terbuka 3 detik!
```

### Langkah 8: Update Config

```bash
nano /var/www/airpanas/api/config/Config.php
```

Ubah baris:
```php
const DOORLOCK_API_URL = 'http://<IP_PI>:5000/door/open';
```

Contoh:
```php
const DOORLOCK_API_URL = 'http://192.168.1.100:5000/door/open';
```

Save: `Ctrl+O`, Enter, `Ctrl+X`

### Langkah 9: Test End-to-End

```bash
cd /var/www/airpanas/api

# Test attendance (akan trigger pintu!)
./quick_test.sh 0319766798 masuk
```

**Expected:**
```json
{
  "status": "success",
  "data": {
    "nama": "Adeli",
    "door_triggered": true  ← HARUS TRUE!
  }
}
```

**DAN PINTU MEMBUKA 3 DETIK! 🎉**

### Langkah 10: Setup Autostart (Optional)

```bash
# SSH ke Pi
ssh pi@<IP_PI>

# Create service
sudo tee /etc/systemd/system/doorlock.service > /dev/null << 'SVCEOF'
[Unit]
Description=Doorlock Server
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/home/pi
ExecStart=/usr/bin/python3 /home/pi/doorlock_server_real.py
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
SVCEOF

# Enable dan start
sudo systemctl daemon-reload
sudo systemctl enable doorlock
sudo systemctl start doorlock

# Check status
sudo systemctl status doorlock

# View logs
journalctl -u doorlock -f
```

---

## 🎯 QUICK TROUBLESHOOTING

| Problem | Solution |
|---------|----------|
| Cannot find Pi IP | Use monitor+keyboard, run `hostname -I` |
| Cannot ping Pi | Check power, network cable, WiFi config |
| SSH refused | Enable via `sudo raspi-config` → SSH |
| Import error | Install: `sudo apt install python3-flask python3-rpi.gpio` |
| Relay no CLICK | Check wiring (5V, GND, GPIO17), try different GPIO |
| Door no open | Check 12V power, test lock directly, verify relay NO contact |
| HTTP timeout | Check firewall: `sudo ufw allow 5000` |
| Server not start | Run with sudo: `sudo python3 ...` |

---

## 📋 CHECKLIST LENGKAP

**Hardware:**
- [ ] Raspberry Pi powered on (red LED)
- [ ] Network connected (ethernet or WiFi)
- [ ] Relay module wired: 5V, GND, GPIO17
- [ ] Door lock connected to relay + 12V power
- [ ] All connections tight and secure

**Software:**
- [ ] Found Pi IP address
- [ ] Can SSH to Pi
- [ ] Python Flask installed
- [ ] RPi.GPIO installed
- [ ] doorlock_server_real.py copied
- [ ] Manual GPIO test SUCCESS (CLICK!)

**Integration:**
- [ ] Server running on port 5000
- [ ] Health check returns success
- [ ] Test endpoint triggers relay
- [ ] Config.php updated with correct IP
- [ ] Attendance API test triggers door
- [ ] door_triggered: true in response

**Production:**
- [ ] Systemd service configured
- [ ] Service autostart enabled
- [ ] Logs monitored
- [ ] Backup plan if Pi offline

---

## 🚀 NEXT STEPS

1. **Cari IP Raspberry Pi** (monitor/router/nmap)
2. **SSH ke Pi** dan install dependencies
3. **Test relay** manual (harus bunyi CLICK)
4. **Copy & run server** 
5. **Test dari curl** (health, test, door/open)
6. **Update config** dengan IP yang benar
7. **Test attendance** → pintu terbuka! 🎉

---

**Need Help?**
- Check `/var/www/airpanas/DOORLOCK_HARDWARE_SETUP.md` untuk detail lengkap
- Run `/var/www/airpanas/api/DOORLOCK_QUICK_COMMANDS.md` untuk command reference

Good luck! 🚪✨
