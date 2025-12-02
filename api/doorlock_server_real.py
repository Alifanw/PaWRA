#!/usr/bin/env python3
# ===============================
# Sistem Absensi + Doorlock IGASAR
# Raspberry Pi + Tkinter + Flask + MySQL
# ===============================

import threading
import time
import logging
from contextlib import contextmanager
from datetime import datetime, date
from flask import Flask, request, jsonify
from flask_cors import CORS

# Try to import GPIO, fallback to mock if not on Raspberry Pi
try:
    import RPi.GPIO as GPIO
    GPIO_AVAILABLE = True
except ImportError:
    print("WARNING: RPi.GPIO not available. Running in SIMULATION mode.")
    GPIO_AVAILABLE = False
    
    # Mock GPIO for testing on non-Pi hardware
    class MockGPIO:
        BCM = 'BCM'
        OUT = 'OUT'
        HIGH = 1
        LOW = 0
        
        @staticmethod
        def setmode(mode): pass
        @staticmethod
        def setwarnings(flag): pass
        @staticmethod
        def setup(pin, mode, initial=0): pass
        @staticmethod
        def output(pin, state): 
            print(f"[MOCK GPIO] Pin {pin} set to {'HIGH' if state else 'LOW'}")
        @staticmethod
        def cleanup(): pass
    
    GPIO = MockGPIO()

# Try to import Tkinter for GUI
try:
    import tkinter as tk
    from tkinter import ttk, messagebox
    TKINTER_AVAILABLE = True
except ImportError:
    print("WARNING: Tkinter not available. GUI disabled.")
    TKINTER_AVAILABLE = False

# Try to import MySQL connector
try:
    import mysql.connector
    MYSQL_AVAILABLE = True
except ImportError:
    print("WARNING: mysql-connector-python not available. Database disabled.")
    MYSQL_AVAILABLE = False

# -------------------------------
# KONFIGURASI DASAR
# -------------------------------
RELAY_PIN = 17               # Pin GPIO untuk relay doorlock
RELAY_ACTIVE_LOW = True      # Sesuaikan modul relay kamu (True = LOW aktif)
DEFAULT_DELAY = 5            # Detik doorlock terbuka
SECURE_TOKEN = "SECURE_KEY_IGASAR"

# Konfigurasi Database
DB_CONFIG = {
    'host': 'website.airpanaswalini.com',
    'user': 'root',
    'password': 'igasarpride',
    'database': 'walini_pj',
    'port': 3306
}

# Initialize GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)
GPIO.setup(RELAY_PIN, GPIO.OUT, initial=GPIO.HIGH if RELAY_ACTIVE_LOW else GPIO.LOW)

# Variabel global
door_status = "Terkunci"
current_delay = DEFAULT_DELAY
status_lock = threading.Lock()  # Lock untuk mengamankan akses door_status
app_flask = Flask(__name__)
CORS(app_flask)  # Izinkan CORS untuk browser kiosk lokal

# Referensi ke komponen GUI (akan diinisialisasi nanti)
status_label_gui = None
log_box_gui = None

# -------------------------------
# LOGGING SETUP
# -------------------------------
logging.basicConfig(
    level=logging.INFO,
    format="[%(asctime)s] %(levelname)s: %(message)s",
    datefmt="%H:%M:%S"
)

# -------------------------------
# FUNGSI DATABASE
# -------------------------------
@contextmanager
def get_db_connection():
    """Konteks manajer untuk koneksi database."""
    if not MYSQL_AVAILABLE:
        logging.warning("MySQL tidak tersedia, melewati operasi database")
        yield None
        return
    
    conn = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        yield conn
    except Exception as e:
        logging.error(f"Error koneksi database: {e}")
        if conn:
            conn.rollback()
        raise
    finally:
        if conn and conn.is_connected():
            conn.close()

def init_db():
    """Inisialisasi database dan tabel yang diperlukan"""
    if not MYSQL_AVAILABLE:
        logging.warning("MySQL tidak tersedia, melewati init_db")
        return
    
    try:
        with get_db_connection() as db:
            if db is None:
                return
            cursor = db.cursor()
            # Pastikan tabel log_absensi dan karyawan ada
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS log_absensi (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kodes VARCHAR(50),
                    nama VARCHAR(100),
                    status VARCHAR(20),
                    waktus DATETIME
                )
            """)
            cursor.execute("""
                CREATE TABLE IF NOT EXISTS karyawan (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    kodes VARCHAR(50) UNIQUE,
                    nama VARCHAR(100)
                )
            """)
            db.commit()
            cursor.close()
        logging.info("Koneksi database berhasil, tabel dicek")
    except Exception as e:
        logging.error(f"Init DB gagal: {e}")
        if TKINTER_AVAILABLE and messagebox:
            messagebox.showerror("Database Error", f"Tidak dapat menginisialisasi database: {e}")

# -------------------------------
# FUNGSI DOOR CONTROL
# -------------------------------
def set_relay(active: bool):
    """Kendalikan relay agar tidak terbalik logikanya."""
    if RELAY_ACTIVE_LOW:
        GPIO.output(RELAY_PIN, GPIO.LOW if active else GPIO.HIGH)
    else:
        GPIO.output(RELAY_PIN, GPIO.HIGH if active else GPIO.LOW)

def open_door(delay=None):
    """
    Trigger door relay for specified duration
    GPIO HIGH = Door unlocked (relay ON)
    GPIO LOW = Door locked (relay OFF)
    """
    with door_lock:
        print(f"[DOOR] Opening for {delay} seconds...")
        
        # Unlock door (relay ON)
        GPIO.output(DOOR_PIN, GPIO.HIGH)
        
        # Wait
        time.sleep(delay)
        
        # Lock door (relay OFF)
        GPIO.output(DOOR_PIN, GPIO.LOW)
        
        print(f"[DOOR] Closed")

@app.route('/door/open', methods=['POST'])
def open_door():
    """
    Open door for specified duration
    
    Request:
        POST /door/open
        Headers:
            Authorization: Bearer SECURE_KEY_IGASAR
            Content-Type: application/json
        Body:
            {
                "delay": 3,  // optional, default 3 seconds
                "kode": "employee_code",  // for logging
                "status": "masuk"  // for logging
            }
    
    Response:
        200: Door opened successfully
        401: Invalid token
        400: Invalid request
        500: Hardware error
    """
    
    # Validate authorization
    auth_header = request.headers.get('Authorization', '')
    if auth_header != f'Bearer {VALID_TOKEN}':
        return jsonify({
            'status': 'error',
            'message': 'Invalid token'
        }), 401
    
    # Get parameters
    try:
        data = request.get_json() or {}
        delay = int(data.get('delay', DEFAULT_DELAY))
        kode = data.get('kode', 'unknown')
        status = data.get('status', 'unknown')
        
        # Validate delay (1-10 seconds)
        if delay < 1 or delay > 10:
            return jsonify({
                'status': 'error',
                'message': 'Delay must be between 1-10 seconds'
            }), 400
        
        # Log request
        timestamp = time.strftime('%Y-%m-%d %H:%M:%S')
        print(f"[{timestamp}] Door trigger request - Code: {kode}, Status: {status}, Delay: {delay}s")
        
        # Trigger door in background thread (non-blocking)
        threading.Thread(target=trigger_door, args=(delay,), daemon=True).start()
        
        return jsonify({
            'status': 'success',
            'message': f'Door opened for {delay} seconds',
            'timestamp': timestamp,
            'gpio_mode': 'REAL' if GPIO_AVAILABLE else 'SIMULATED'
        })
        
    except Exception as e:
        print(f"[ERROR] {str(e)}")
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 500

@app.route('/door/status', methods=['GET'])
def door_status():
    """Get current door status"""
    return jsonify({
        'status': 'success',
        'door_status': 'locked',  # Would need sensor to detect actual state
        'gpio_available': GPIO_AVAILABLE,
        'pin': DOOR_PIN,
        'timestamp': time.strftime('%Y-%m-%d %H:%M:%S')
    })

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'server': 'doorlock_real',
        'version': '2.0',
        'gpio_mode': 'REAL' if GPIO_AVAILABLE else 'SIMULATED'
    })

@app.route('/test', methods=['POST'])
def test_relay():
    """Test relay (admin only) - quick on/off"""
    auth_header = request.headers.get('Authorization', '')
    if auth_header != f'Bearer {VALID_TOKEN}':
        return jsonify({'status': 'error', 'message': 'Unauthorized'}), 401
    
    print("[TEST] Testing relay...")
    GPIO.output(DOOR_PIN, GPIO.HIGH)
    time.sleep(0.5)
    GPIO.output(DOOR_PIN, GPIO.LOW)
    
    return jsonify({
        'status': 'success',
        'message': 'Relay test completed'
    })

def cleanup():
    """Cleanup GPIO on shutdown"""
    print("[SHUTDOWN] Cleaning up GPIO...")
    GPIO.cleanup()

if __name__ == '__main__':
    import atexit
    atexit.register(cleanup)
    
    print("=" * 60)
    print("RASPBERRY PI DOORLOCK SERVER - REAL HARDWARE")
    print("=" * 60)
    print(f"GPIO Mode: {'REAL HARDWARE' if GPIO_AVAILABLE else 'SIMULATED (no GPIO)'}")
    print(f"GPIO Pin: {DOOR_PIN} (BCM)")
    print(f"Token: {VALID_TOKEN}")
    print(f"Default Delay: {DEFAULT_DELAY} seconds")
    print("-" * 60)
    print("Endpoints:")
    print("  POST /door/open   - Trigger door unlock")
    print("  GET  /door/status - Get door status")
    print("  GET  /health      - Health check")
    print("  POST /test        - Test relay (quick pulse)")
    print("-" * 60)
    
    try:
        app.run(host='0.0.0.0', port=5000, debug=False)
    except KeyboardInterrupt:
        print("\n[SHUTDOWN] Server stopped by user")
        cleanup()
