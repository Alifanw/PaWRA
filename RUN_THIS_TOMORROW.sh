#!/bin/bash

# CRITICAL - RUN THIS EXACTLY TOMORROW
# Copy & paste into terminal 30 minutes before presentation

cd /var/www/airpanas
pkill -f 'php artisan serve' || true
sleep 2
php artisan migrate:fresh --seed
php artisan cache:clear
php artisan config:clear
php artisan serve --host=0.0.0.0 --port=8000

# Then open browser: http://localhost:8000/login
# Login: admin / 123123
# Expected: Dashboard in ~300ms
