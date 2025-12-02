#!/bin/bash
# Raspberry Pi Setup Helper
# Usage: ./setup_pi.sh <IP_ADDRESS>

PI_IP="$1"

if [ -z "$PI_IP" ]; then
    echo "Usage: $0 <RASPBERRY_PI_IP>"
    echo ""
    echo "Example: $0 192.168.1.100"
    echo ""
    echo "💡 Cara cari IP Raspberry Pi:"
    echo "   1. Hubungkan monitor+keyboard ke Pi"
    echo "   2. Login: pi / raspberry"
    echo "   3. Run: hostname -I"
    echo ""
    exit 1
fi

echo "════════════════════════════════════════════════════════"
echo "  🚀 RASPBERRY PI DOORLOCK AUTO SETUP"
echo "════════════════════════════════════════════════════════"
echo ""
echo "Target IP: $PI_IP"
echo ""

# Step 1: Test connectivity
echo "📡 Step 1: Testing connectivity..."
if ping -c 2 "$PI_IP" > /dev/null 2>&1; then
    echo "   ✅ Raspberry Pi reachable"
else
    echo "   ❌ Cannot reach $PI_IP"
    echo "   Check: Power on, network connected, correct IP"
    exit 1
fi

# Step 2: Test SSH
echo ""
echo "🔐 Step 2: Testing SSH connection..."
if ssh -o ConnectTimeout=5 -o BatchMode=yes "pi@$PI_IP" exit 2>/dev/null; then
    echo "   ✅ SSH connection OK (key-based)"
elif ssh -o ConnectTimeout=5 "pi@$PI_IP" exit 2>/dev/null; then
    echo "   ✅ SSH connection OK (password)"
else
    echo "   ⚠️  SSH connection failed"
    echo "   Try manually: ssh pi@$PI_IP"
    echo "   If refused, enable SSH on Pi: sudo raspi-config"
    read -p "   Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Step 3: Copy server file
echo ""
echo "📦 Step 3: Copying doorlock_server_real.py..."
if scp doorlock_server_real.py "pi@$PI_IP:/home/pi/"; then
    echo "   ✅ File copied successfully"
else
    echo "   ❌ Failed to copy file"
    exit 1
fi

# Step 4: Install dependencies (optional - requires interaction)
echo ""
echo "📚 Step 4: Install dependencies on Pi..."
echo "   Running: sudo apt update && sudo apt install -y python3-flask python3-rpi.gpio"
echo ""
read -p "   Install now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ssh "pi@$PI_IP" "sudo apt update && sudo apt install -y python3-flask python3-rpi.gpio"
    echo "   ✅ Dependencies installed"
else
    echo "   ⏭️  Skipped (install manually later)"
fi

# Step 5: Test relay (optional)
echo ""
echo "🔧 Step 5: Test relay GPIO..."
read -p "   Test relay now? (GPIO will trigger - y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "   Testing GPIO17 relay..."
    ssh "pi@$PI_IP" "sudo python3 << 'PYEOF'
import RPi.GPIO as GPIO
import time

print('→ Testing relay on GPIO17...')
GPIO.setmode(GPIO.BCM)
GPIO.setup(17, GPIO.OUT)

print('→ Relay ON (should CLICK)')
GPIO.output(17, GPIO.HIGH)
time.sleep(2)

print('→ Relay OFF')
GPIO.output(17, GPIO.LOW)

GPIO.cleanup()
print('✅ Test complete!')
PYEOF"
    echo "   Did you hear relay CLICK? Check LED on relay module."
else
    echo "   ⏭️  Skipped"
fi

# Step 6: Start server in background
echo ""
echo "🚀 Step 6: Starting doorlock server..."
read -p "   Start server now? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    ssh "pi@$PI_IP" "sudo nohup python3 /home/pi/doorlock_server_real.py > /tmp/doorlock.log 2>&1 &"
    sleep 2
    echo "   ✅ Server started in background"
    echo "   View logs: ssh pi@$PI_IP 'tail -f /tmp/doorlock.log'"
else
    echo "   ⏭️  Skipped"
    echo "   Start manually: ssh pi@$PI_IP 'sudo python3 /home/pi/doorlock_server_real.py'"
fi

# Step 7: Test server
echo ""
echo "🧪 Step 7: Testing server endpoints..."
sleep 1

echo -n "   Health check: "
if curl -s "http://$PI_IP:5000/health" | grep -q "healthy"; then
    echo "✅ OK"
else
    echo "❌ FAILED"
fi

echo -n "   Test relay: "
if curl -s -X POST "http://$PI_IP:5000/test" \
    -H "Authorization: Bearer SECURE_KEY_IGASAR" | grep -q "success"; then
    echo "✅ OK (should hear CLICK)"
else
    echo "❌ FAILED"
fi

# Step 8: Update config
echo ""
echo "⚙️  Step 8: Update Config.php..."
CONFIG_FILE="/var/www/airpanas/api/config/Config.php"
if grep -q "DOORLOCK_API_URL" "$CONFIG_FILE"; then
    echo "   Current URL: $(grep DOORLOCK_API_URL $CONFIG_FILE | grep -oP 'http://[^;]*')"
    echo "   New URL: http://$PI_IP:5000/door/open"
    read -p "   Update config? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        # Backup
        cp "$CONFIG_FILE" "${CONFIG_FILE}.backup.$(date +%s)"
        # Update
        sed -i "s|const DOORLOCK_API_URL = .*|const DOORLOCK_API_URL = 'http://$PI_IP:5000/door/open';|" "$CONFIG_FILE"
        echo "   ✅ Config updated (backup created)"
    else
        echo "   ⏭️  Skipped"
    fi
else
    echo "   ⚠️  Config file not found or incorrect format"
fi

# Step 9: Test end-to-end
echo ""
echo "🎯 Step 9: Test attendance → door trigger..."
read -p "   Test full flow now? (Door will open! - y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo ""
    ./quick_test.sh 0319766798 masuk
    echo ""
    echo "   Check response: door_triggered should be TRUE"
    echo "   Check physically: Door should have opened for 3 seconds"
else
    echo "   ⏭️  Skipped"
    echo "   Test manually: ./quick_test.sh 0319766798 masuk"
fi

# Summary
echo ""
echo "════════════════════════════════════════════════════════"
echo "  ✅ SETUP COMPLETE!"
echo "════════════════════════════════════════════════════════"
echo ""
echo "📋 Next steps:"
echo "   1. Test attendance: ./quick_test.sh <employee_code> masuk"
echo "   2. Monitor logs: ssh pi@$PI_IP 'tail -f /tmp/doorlock.log'"
echo "   3. Setup autostart: See DOORLOCK_HARDWARE_SETUP.md"
echo ""
echo "🔧 Useful commands:"
echo "   ssh pi@$PI_IP                 - Connect to Pi"
echo "   curl http://$PI_IP:5000/health  - Check server"
echo "   tail -f /var/www/airpanas/api/logs/doorlock.log  - Local logs"
echo ""
echo "📚 Documentation:"
echo "   /var/www/airpanas/api/README.md"
echo "   /var/www/airpanas/DOORLOCK_HARDWARE_SETUP.md"
echo ""

exit 0
