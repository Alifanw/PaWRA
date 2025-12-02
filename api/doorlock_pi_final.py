#!/usr/bin/env python3
"""
Doorlock + Absensi System - Version 3.0 FINAL
Untuk Raspberry Pi dengan database schema baru (employees, attendance_logs)

Hardware:
- GPIO Pin 2 untuk relay doorlock
- RFID reader untuk absensi otomatis

Database Schema:
- employees (id, code, name, is_active)
- attendance_logs (id, employee_id, status, event_time)
"""

import tkinter as tk
from tkinter import messagebox, ttk
from datetime import datetime, timedelta
import threading
import time
import mysql.connector
from mysql.connector import Error
from flask import Flask, request, jsonify
from flask_cors import CORS

# Import GPIO dengan error handling
try:
    import RPi.GPIO as GPIO
    GPIO_AVAILABLE = True
    print("✓ GPIO Module tersedia")
except (ImportError, RuntimeError) as e:
    GPIO_AVAILABLE = False
    print(f"⚠ GPIO Module tidak tersedia: {e}")
    print("⚠ Menggunakan mode SIMULASI")

# =============================================================================
# KONFIGURASI
# =============================================================================

# Database Configuration
DB_CONFIG = {
    'host': 'website.airpanaswalini.com',
    'user': 'root',
    'password': 'igasarpride',
    'database': 'walini_pj',
    'port': 3306,
    'ssl_disabled': True
}

# GPIO Configuration
RELAY_PIN = 2  # GPIO 2 (Physical Pin 3)
RELAY_ACTIVE_LOW = True  # True jika relay aktif saat LOW

# Doorlock Configuration
DEFAULT_DOOR_DELAY = 5  # Detik
AUTO_LOCK_AFTER_ABSEN = True  # Auto buka pintu setelah absen

# API Configuration
API_TOKEN = "SECURE_KEY_IGASAR"
FLASK_PORT = 5000
FLASK_HOST = "0.0.0.0"

# Status Mapping (GUI format -> Database ENUM)
STATUS_MAPPING = {
    "Masuk": "masuk",
    "Pulang": "pulang",
    "Lembur": "lembur",
    "Pulang Lembur": "pulang_lembur"
}

# =============================================================================
# DATABASE CONNECTION
# =============================================================================

def connect_db():
    """Buat koneksi ke database dengan retry"""
    max_retries = 3
    retry_delay = 2
    
    for attempt in range(max_retries):
        try:
            conn = mysql.connector.connect(**DB_CONFIG)
            if conn.is_connected():
                print(f"✓ Database connected: {DB_CONFIG['database']}")
                return conn
        except Error as e:
            print(f"⚠ Database connection attempt {attempt + 1} failed: {e}")
            if attempt < max_retries - 1:
                time.sleep(retry_delay)
    
    raise Exception("Gagal koneksi ke database setelah beberapa percobaan")

# Global database connection
db_conn = None
db_cursor = None

def init_db():
    """Inisialisasi database connection dan verifikasi tabel"""
    global db_conn, db_cursor
    
    try:
        db_conn = connect_db()
        db_cursor = db_conn.cursor(dictionary=True)
        
        # Verifikasi tabel employees
        db_cursor.execute("SHOW TABLES LIKE 'employees'")
        if not db_cursor.fetchone():
            raise Exception("Tabel 'employees' tidak ditemukan!")
        
        # Verifikasi tabel attendance_logs
        db_cursor.execute("SHOW TABLES LIKE 'attendance_logs'")
        if not db_cursor.fetchone():
            raise Exception("Tabel 'attendance_logs' tidak ditemukan!")
        
        print("✓ Tabel database terverifikasi: employees, attendance_logs")
        
    except Error as e:
        print(f"✗ Database initialization error: {e}")
        raise

# =============================================================================
# GPIO CONTROL
# =============================================================================

def init_gpio():
    """Inisialisasi GPIO untuk relay doorlock"""
    if not GPIO_AVAILABLE:
        print("⚠ GPIO tidak tersedia - Mode SIMULASI aktif")
        return False
    
    try:
        GPIO.setmode(GPIO.BCM)
        GPIO.setwarnings(False)
        GPIO.setup(RELAY_PIN, GPIO.OUT)
        
        # Set initial state (locked)
        if RELAY_ACTIVE_LOW:
            GPIO.output(RELAY_PIN, GPIO.HIGH)  # HIGH = OFF (locked)
        else:
            GPIO.output(RELAY_PIN, GPIO.LOW)
        
        print(f"✓ GPIO initialized: Pin {RELAY_PIN} (Active {'LOW' if RELAY_ACTIVE_LOW else 'HIGH'})")
        return True
        
    except Exception as e:
        print(f"✗ GPIO initialization error: {e}")
        return False

def set_relay(active: bool):
    """Kontrol relay doorlock"""
    if not GPIO_AVAILABLE:
        status = "ACTIVE" if active else "INACTIVE"
        print(f"[SIMULASI] Relay {status}")
        return
    
    try:
        if RELAY_ACTIVE_LOW:
            # Active LOW: LOW = ON (unlocked), HIGH = OFF (locked)
            GPIO.output(RELAY_PIN, GPIO.LOW if active else GPIO.HIGH)
        else:
            # Active HIGH: HIGH = ON (unlocked), LOW = OFF (locked)
            GPIO.output(RELAY_PIN, GPIO.HIGH if active else GPIO.LOW)
        
        status = "UNLOCKED" if active else "LOCKED"
        print(f"✓ Doorlock {status} (GPIO {RELAY_PIN})")
        
    except Exception as e:
        print(f"✗ Relay control error: {e}")

def cleanup_gpio():
    """Cleanup GPIO saat program selesai"""
    if GPIO_AVAILABLE:
        try:
            GPIO.cleanup()
            print("✓ GPIO cleanup completed")
        except Exception as e:
            print(f"⚠ GPIO cleanup error: {e}")

# =============================================================================
# DOORLOCK LOGIC
# =============================================================================

class DoorlockController:
    """Controller untuk doorlock dengan timer otomatis"""
    
    def __init__(self):
        self.is_locked = True
        self.timer_thread = None
        self.lock_timer = None
        
    def unlock(self, delay_seconds=DEFAULT_DOOR_DELAY):
        """Buka pintu dengan timer otomatis"""
        if not self.is_locked:
            print("⚠ Pintu sudah terbuka")
            return False
        
        # Buka pintu
        set_relay(True)
        self.is_locked = False
        print(f"🔓 Pintu DIBUKA (akan terkunci otomatis dalam {delay_seconds} detik)")
        
        # Cancel timer lama jika ada
        if self.lock_timer:
            self.lock_timer.cancel()
        
        # Set timer untuk kunci otomatis
        self.lock_timer = threading.Timer(delay_seconds, self._auto_lock)
        self.lock_timer.start()
        
        return True
    
    def _auto_lock(self):
        """Kunci pintu otomatis (dipanggil oleh timer)"""
        self.lock()
        print("🔒 Pintu TERKUNCI otomatis")
    
    def lock(self):
        """Kunci pintu manual"""
        if self.is_locked:
            return False
        
        set_relay(False)
        self.is_locked = True
        
        if self.lock_timer:
            self.lock_timer.cancel()
            self.lock_timer = None
        
        return True
    
    def get_status(self):
        """Ambil status pintu"""
        return {
            "is_locked": self.is_locked,
            "status": "TERKUNCI" if self.is_locked else "TERBUKA",
            "gpio_mode": "HARDWARE" if GPIO_AVAILABLE else "SIMULATED"
        }

# Global doorlock instance
doorlock = DoorlockController()

# =============================================================================
# ABSENSI LOGIC
# =============================================================================

def proses_absensi(kode: str, status_absen: str):
    """
    Proses absensi karyawan dengan database schema baru
    
    Args:
        kode: Employee code (e.g., EMP001)
        status_absen: Status GUI format (Masuk/Pulang/Lembur/Pulang Lembur)
    
    Returns:
        dict: {"status": "success/error/info", "message": "...", "data": {...}}
    """
    global db_conn, db_cursor
    
    try:
        # Reconnect jika koneksi hilang
        if not db_conn or not db_conn.is_connected():
            print("⚠ Database reconnecting...")
            init_db()
        
        # Convert status GUI ke database ENUM
        status_db = STATUS_MAPPING.get(status_absen)
        if not status_db:
            return {
                "status": "error",
                "message": f"Status tidak valid: {status_absen}"
            }
        
        # 1. VALIDASI EMPLOYEE
        db_cursor.execute(
            "SELECT id, code, name, is_active FROM employees WHERE code = %s",
            (kode,)
        )
        employee = db_cursor.fetchone()
        
        if not employee:
            return {
                "status": "error",
                "message": f"Kode karyawan {kode} tidak ditemukan"
            }
        
        # Cek apakah karyawan aktif
        if not employee["is_active"]:
            return {
                "status": "error",
                "message": f"{employee['name']} sudah tidak aktif"
            }
        
        employee_id = employee["id"]
        employee_name = employee["name"]
        
        # 2. CEK LOG HARI INI
        sekarang = datetime.now()
        hari_ini = sekarang.strftime('%Y-%m-%d')
        
        db_cursor.execute("""
            SELECT status, event_time 
            FROM attendance_logs 
            WHERE employee_id = %s 
              AND DATE(event_time) = %s
            ORDER BY event_time DESC 
            LIMIT 1
        """, (employee_id, hari_ini))
        
        last_log = db_cursor.fetchone()
        
        # 3. VALIDASI DUPLIKASI
        if last_log:
            last_status = last_log["status"]
            
            # Cek duplikasi status yang sama
            if last_status == status_db:
                return {
                    "status": "info",
                    "message": f"{employee_name} sudah absen {status_absen.lower()} hari ini.",
                    "data": {
                        "employee": employee_name,
                        "last_time": last_log["event_time"].strftime('%H:%M:%S')
                    }
                }
            
            # Validasi urutan status
            if status_db == "masuk" and last_status in ["pulang", "pulang_lembur"]:
                return {
                    "status": "error",
                    "message": f"{employee_name} sudah pulang hari ini, tidak bisa absen masuk lagi."
                }
        
        # 4. INSERT LOG BARU
        db_cursor.execute(
            "INSERT INTO attendance_logs (employee_id, status, event_time) VALUES (%s, %s, %s)",
            (employee_id, status_db, sekarang)
        )
        db_conn.commit()
        
        print(f"✓ Absensi tersimpan: {employee_name} - {status_absen} ({sekarang.strftime('%Y-%m-%d %H:%M:%S')})")
        
        # 5. TRIGGER DOORLOCK (jika enabled)
        door_opened = False
        if AUTO_LOCK_AFTER_ABSEN:
            door_opened = doorlock.unlock(DEFAULT_DOOR_DELAY)
        
        return {
            "status": "success",
            "message": f"Berhasil! {employee_name} absen {status_absen.lower()}.",
            "data": {
                "employee_id": employee_id,
                "employee_name": employee_name,
                "employee_code": kode,
                "status": status_db,
                "event_time": sekarang.strftime('%Y-%m-%d %H:%M:%S'),
                "door_opened": door_opened
            }
        }
        
    except Error as e:
        db_conn.rollback()
        print(f"✗ Database error: {e}")
        return {
            "status": "error",
            "message": f"Database error: {str(e)}"
        }
    except Exception as e:
        print(f"✗ Unexpected error: {e}")
        return {
            "status": "error",
            "message": f"System error: {str(e)}"
        }

# =============================================================================
# FLASK API
# =============================================================================

app = Flask(__name__)
CORS(app)

def verify_token(token):
    """Verifikasi API token"""
    return token == API_TOKEN

@app.route('/', methods=['GET'])
def index():
    """API Info"""
    return jsonify({
        "name": "Doorlock + Absensi API",
        "version": "3.0",
        "features": ["doorlock", "attendance", "auto-lock"],
        "endpoints": ["/health", "/door/open", "/door/lock", "/door/status", "/absen"]
    })

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    db_status = "AVAILABLE" if (db_conn and db_conn.is_connected()) else "UNAVAILABLE"
    
    return jsonify({
        "status": "healthy",
        "timestamp": datetime.now().isoformat(),
        "gpio_mode": "HARDWARE" if GPIO_AVAILABLE else "SIMULATED",
        "mysql": db_status,
        "doorlock": doorlock.get_status()
    })

@app.route('/door/open', methods=['POST'])
def api_door_open():
    """Buka pintu via API"""
    data = request.get_json() or {}
    
    # Verifikasi token
    token = data.get('token') or request.headers.get('X-API-Token')
    if not verify_token(token):
        return jsonify({"status": "error", "message": "Invalid token"}), 401
    
    # Ambil delay dari request (default 5 detik)
    delay = int(data.get('delay', DEFAULT_DOOR_DELAY))
    delay = max(1, min(delay, 30))  # Batasi 1-30 detik
    
    # Buka pintu
    success = doorlock.unlock(delay)
    
    if success:
        return jsonify({
            "status": "success",
            "message": f"Pintu dibuka, akan terkunci otomatis dalam {delay} detik",
            "delay_used": delay
        })
    else:
        return jsonify({
            "status": "info",
            "message": "Pintu sudah terbuka"
        })

@app.route('/door/lock', methods=['POST'])
def api_door_lock():
    """Kunci pintu via API"""
    data = request.get_json() or {}
    
    # Verifikasi token
    token = data.get('token') or request.headers.get('X-API-Token')
    if not verify_token(token):
        return jsonify({"status": "error", "message": "Invalid token"}), 401
    
    success = doorlock.lock()
    
    if success:
        return jsonify({"status": "success", "message": "Pintu dikunci"})
    else:
        return jsonify({"status": "info", "message": "Pintu sudah terkunci"})

@app.route('/door/status', methods=['GET'])
def api_door_status():
    """Cek status pintu"""
    return jsonify(doorlock.get_status())

@app.route('/absen', methods=['POST'])
def api_absen():
    """Endpoint absensi via API"""
    data = request.get_json() or {}
    
    # Verifikasi token
    token = data.get('token') or request.headers.get('X-API-Token')
    if not verify_token(token):
        return jsonify({"status": "error", "message": "Invalid token"}), 401
    
    # Ambil parameter
    kode = data.get('kode', '').strip()
    status = data.get('status', '').strip()
    
    if not kode or not status:
        return jsonify({
            "status": "error",
            "message": "Parameter 'kode' dan 'status' wajib diisi"
        }), 400
    
    # Proses absensi
    result = proses_absensi(kode, status)
    
    http_code = 200 if result["status"] == "success" else 400
    return jsonify(result), http_code

def run_flask():
    """Jalankan Flask server di background thread"""
    print(f"\n🌐 Starting Flask API server on {FLASK_HOST}:{FLASK_PORT}")
    app.run(host=FLASK_HOST, port=FLASK_PORT, debug=False, use_reloader=False)

# =============================================================================
# TKINTER GUI
# =============================================================================

class AbsensiGUI:
    """GUI untuk sistem absensi + doorlock"""
    
    def __init__(self, master):
        self.master = master
        master.title("Sistem Absensi + Doorlock v3.0")
        master.geometry("600x500")
        master.configure(bg="#f0f0f0")
        
        # Variabel
        self.kode_var = tk.StringVar()
        self.status_var = tk.StringVar(value="Masuk")
        
        # UI Components
        self.create_widgets()
        
        # Update status doorlock setiap 1 detik
        self.update_door_status()
    
    def create_widgets(self):
        """Buat komponen UI"""
        
        # HEADER
        header_frame = tk.Frame(self.master, bg="#2196F3", height=80)
        header_frame.pack(fill=tk.X)
        
        tk.Label(
            header_frame,
            text="🚪 Sistem Absensi + Doorlock",
            font=("Arial", 20, "bold"),
            bg="#2196F3",
            fg="white"
        ).pack(pady=20)
        
        # MAIN CONTAINER
        main_frame = tk.Frame(self.master, bg="#f0f0f0", padx=30, pady=20)
        main_frame.pack(fill=tk.BOTH, expand=True)
        
        # INPUT KODE KARYAWAN
        tk.Label(
            main_frame,
            text="Kode Karyawan:",
            font=("Arial", 12),
            bg="#f0f0f0"
        ).grid(row=0, column=0, sticky=tk.W, pady=10)
        
        self.kode_entry = tk.Entry(
            main_frame,
            textvariable=self.kode_var,
            font=("Arial", 14),
            width=25
        )
        self.kode_entry.grid(row=0, column=1, pady=10, padx=10)
        self.kode_entry.focus()
        
        # PILIHAN STATUS
        tk.Label(
            main_frame,
            text="Status:",
            font=("Arial", 12),
            bg="#f0f0f0"
        ).grid(row=1, column=0, sticky=tk.W, pady=10)
        
        status_frame = tk.Frame(main_frame, bg="#f0f0f0")
        status_frame.grid(row=1, column=1, sticky=tk.W, pady=10, padx=10)
        
        statuses = ["Masuk", "Pulang", "Lembur", "Pulang Lembur"]
        for status in statuses:
            tk.Radiobutton(
                status_frame,
                text=status,
                variable=self.status_var,
                value=status,
                font=("Arial", 11),
                bg="#f0f0f0"
            ).pack(side=tk.LEFT, padx=5)
        
        # TOMBOL ABSEN
        tk.Button(
            main_frame,
            text="✓ ABSEN",
            command=self.submit_absensi,
            font=("Arial", 14, "bold"),
            bg="#4CAF50",
            fg="white",
            width=20,
            height=2,
            cursor="hand2"
        ).grid(row=2, column=0, columnspan=2, pady=20)
        
        # SEPARATOR
        ttk.Separator(main_frame, orient=tk.HORIZONTAL).grid(
            row=3, column=0, columnspan=2, sticky="ew", pady=15
        )
        
        # DOORLOCK CONTROLS
        door_frame = tk.Frame(main_frame, bg="#f0f0f0")
        door_frame.grid(row=4, column=0, columnspan=2, pady=10)
        
        tk.Label(
            door_frame,
            text="Kontrol Pintu:",
            font=("Arial", 12, "bold"),
            bg="#f0f0f0"
        ).pack()
        
        button_frame = tk.Frame(door_frame, bg="#f0f0f0")
        button_frame.pack(pady=10)
        
        tk.Button(
            button_frame,
            text="🔓 Buka Pintu",
            command=self.buka_pintu,
            font=("Arial", 12),
            bg="#2196F3",
            fg="white",
            width=15,
            cursor="hand2"
        ).pack(side=tk.LEFT, padx=5)
        
        tk.Button(
            button_frame,
            text="🔒 Kunci Pintu",
            command=self.kunci_pintu,
            font=("Arial", 12),
            bg="#f44336",
            fg="white",
            width=15,
            cursor="hand2"
        ).pack(side=tk.LEFT, padx=5)
        
        # STATUS PINTU
        self.door_status_label = tk.Label(
            door_frame,
            text="Status: TERKUNCI",
            font=("Arial", 14, "bold"),
            bg="#f0f0f0",
            fg="#f44336"
        )
        self.door_status_label.pack(pady=10)
        
        # BIND ENTER KEY
        self.kode_entry.bind('<Return>', lambda e: self.submit_absensi())
    
    def submit_absensi(self):
        """Submit absensi"""
        kode = self.kode_var.get().strip()
        status = self.status_var.get()
        
        if not kode:
            messagebox.showerror("Error", "Kode karyawan harus diisi!")
            return
        
        # Proses absensi
        result = proses_absensi(kode, status)
        
        # Tampilkan hasil
        if result["status"] == "success":
            messagebox.showinfo("Berhasil", result["message"])
            self.kode_var.set("")  # Clear input
            self.kode_entry.focus()
        elif result["status"] == "info":
            messagebox.showinfo("Info", result["message"])
        else:
            messagebox.showerror("Error", result["message"])
    
    def buka_pintu(self):
        """Buka pintu manual"""
        success = doorlock.unlock(DEFAULT_DOOR_DELAY)
        if success:
            messagebox.showinfo("Info", f"Pintu dibuka manual via GUI\nAkan terkunci otomatis dalam {DEFAULT_DOOR_DELAY} detik")
        else:
            messagebox.showinfo("Info", "Pintu sudah terbuka")
    
    def kunci_pintu(self):
        """Kunci pintu manual"""
        success = doorlock.lock()
        if success:
            messagebox.showinfo("Info", "Pintu dikunci kembali")
        else:
            messagebox.showinfo("Info", "Pintu sudah terkunci")
    
    def update_door_status(self):
        """Update label status pintu"""
        status = doorlock.get_status()
        
        if status["is_locked"]:
            self.door_status_label.config(
                text="Status: TERKUNCI 🔒",
                fg="#f44336"
            )
        else:
            self.door_status_label.config(
                text="Status: TERBUKA 🔓",
                fg="#4CAF50"
            )
        
        # Update setiap 500ms
        self.master.after(500, self.update_door_status)

# =============================================================================
# MAIN PROGRAM
# =============================================================================

def main():
    """Main program entry point"""
    
    print("="*60)
    print("  DOORLOCK + ABSENSI SYSTEM v3.0")
    print("="*60)
    
    # 1. Initialize Database
    print("\n[1/4] Initializing Database...")
    try:
        init_db()
    except Exception as e:
        print(f"✗ FATAL: Database initialization failed: {e}")
        return
    
    # 2. Initialize GPIO
    print("\n[2/4] Initializing GPIO...")
    gpio_ok = init_gpio()
    if not gpio_ok:
        print("⚠ Warning: GPIO not available, using simulation mode")
    
    # 3. Start Flask API Server
    print("\n[3/4] Starting Flask API Server...")
    flask_thread = threading.Thread(target=run_flask, daemon=True)
    flask_thread.start()
    time.sleep(1)  # Beri waktu Flask untuk start
    
    # 4. Start GUI
    print("\n[4/4] Starting GUI...")
    try:
        root = tk.Tk()
        gui = AbsensiGUI(root)
        
        print("\n" + "="*60)
        print("  ✓ SISTEM SIAP!")
        print("="*60)
        print(f"  Flask API  : http://0.0.0.0:{FLASK_PORT}")
        print(f"  GPIO Mode  : {'HARDWARE' if GPIO_AVAILABLE else 'SIMULATED'}")
        print(f"  Database   : {DB_CONFIG['database']}@{DB_CONFIG['host']}")
        print("="*60 + "\n")
        
        # Jalankan GUI (blocking)
        root.mainloop()
        
    except KeyboardInterrupt:
        print("\n\n⚠ Program dihentikan oleh user (Ctrl+C)")
    except Exception as e:
        print(f"\n✗ GUI Error: {e}")
    finally:
        # Cleanup
        print("\n🧹 Cleaning up...")
        cleanup_gpio()
        if db_conn and db_conn.is_connected():
            db_cursor.close()
            db_conn.close()
            print("✓ Database connection closed")
        print("✓ Program terminated\n")

if __name__ == "__main__":
    main()
