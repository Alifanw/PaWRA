#!/bin/bash

# SETUP FOR PRESENTATION TOMORROW
# Run these commands exactly as shown

echo "════════════════════════════════════════════════════════════════"
echo "  SETUP FOR PRESENTATION - COPY & PASTE THESE COMMANDS"
echo "════════════════════════════════════════════════════════════════"
echo ""

echo "Step 1: Navigate to project"
echo "  cd /var/www/airpanas"
echo ""

echo "Step 2: Kill any running servers"
echo "  pkill -f 'php artisan serve' || true"
echo ""

echo "Step 3: Fresh database"
echo "  php artisan migrate:fresh --seed"
echo ""

echo "Step 4: Clear caches"
echo "  php artisan cache:clear"
echo "  php artisan config:clear"
echo ""

echo "Step 5: Start server"
echo "  php artisan serve --host=0.0.0.0 --port=8000"
echo ""

echo "════════════════════════════════════════════════════════════════"
echo "  THEN: Open browser to http://localhost:8000/login"
echo "════════════════════════════════════════════════════════════════"
echo ""

echo "LOGIN CREDENTIALS:"
echo "  Username: admin"
echo "  Password: 123123"
echo ""

echo "════════════════════════════════════════════════════════════════"
echo "  FULL SETUP SCRIPT (Run this entire block):"
echo "════════════════════════════════════════════════════════════════"
echo ""

cat << 'SETUP'

#!/bin/bash
cd /var/www/airpanas
pkill -f 'php artisan serve' || true
sleep 2
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
php artisan serve --host=0.0.0.0 --port=8000

# Then open browser: http://localhost:8000/login
# Login with: admin / 123123

SETUP

echo ""
echo "════════════════════════════════════════════════════════════════"
echo "✅ READY FOR PRESENTATION!"
echo "════════════════════════════════════════════════════════════════"
