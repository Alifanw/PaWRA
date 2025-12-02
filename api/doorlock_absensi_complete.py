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
RELAY_PIN = 2                # Pin GPIO untuk relay doorlock
RELAY_ACTIVE_LOW = True      # Sesuaikan modul relay kamu (True = LOW aktif)
DEFAULT_DELAY = 5            # Detik doorlock terbuka
SECURE_TOKEN = "SECURE_KEY_IGASAR"

# Konfigurasi Database
DB_CONFIG = {
    'host': 'website.airpanaswalini.com',
    'user': 'root',
    'password': 'igasarpride',
    'database': 'walini_pj',
    'port': 3306,
    'ssl_disabled': True
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
    """Inisialisasi dan verifikasi koneksi database"""
    if not MYSQL_AVAILABLE:
        logging.warning("MySQL tidak tersedia, melewati init_db")
        return
    
    try:
        with get_db_connection() as db:
            if db is None:
                return
            cursor = db.cursor()
            # Verifikasi tabel employees dan attendance_logs ada
            cursor.execute("SHOW TABLES LIKE 'employees'")
            if not cursor.fetchone():
                logging.error("Tabel 'employees' tidak ditemukan!")
                raise Exception("Tabel employees tidak ada di database")
            
            cursor.execute("SHOW TABLES LIKE 'attendance_logs'")
            if not cursor.fetchone():
                logging.error("Tabel 'attendance_logs' tidak ditemukan!")
                raise Exception("Tabel attendance_logs tidak ada di database")
            
            cursor.close()
        logging.info("Koneksi database berhasil, tabel terverifikasi")
    except Exception as e:
        logging.error(f"Init DB gagal: {e}")
        if TKINTER_AVAILABLE:
            try:
                messagebox.showerror("Database Error", f"Tidak dapat menginisialisasi database: {e}")
            except:
                pass

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
    Fungsi utama untuk membuka pintu dan menutupnya setelah delay.
    Memicu buka/tutup otomatis sesuai permintaan.
    """
    global door_status, current_delay
    if delay is None:
        delay = current_delay
    try:
        d = float(delay)
        if d <= 0 or d > 10:
            d = current_delay
    except (ValueError, TypeError):
        d = current_delay

    with status_lock:  # Gunakan lock untuk mengamankan akses status
        if door_status == "Terbuka":
            logging.info("Pintu sedang terbuka, permintaan dibatalkan.")
            return  # Jika sudah terbuka, jangan buka lagi

        door_status = "Terbuka"
        logging.info(f"Pintu dibuka selama {d:.2f} detik")
        set_relay(True)
        update_status_label()  # Update GUI
        log_message(f"Pintu dibuka selama {d:.2f} detik")  # Log ke GUI

    time.sleep(d)  # Tunggu selama delay

    with status_lock:  # Gunakan lock untuk mengamankan penutupan
        set_relay(False)
        door_status = "Terkunci"
        logging.info("Pintu dikunci kembali.")
        update_status_label()  # Update GUI
        log_message("Pintu dikunci kembali.")  # Log ke GUI

def update_status_label():
    """Update label status di GUI."""
    if status_label_gui:
        try:
            if door_status == "Terbuka":
                status_label_gui.config(text="TERBUKA", fg="green")
            else:
                status_label_gui.config(text="TERKUNCI", fg="red")
        except:
            pass

def log_message(msg):
    """Tambahkan pesan ke log box di GUI."""
    if log_box_gui:
        try:
            log_box_gui.insert("end", f"{datetime.now().strftime('%H:%M:%S')} - {msg}\n")
            log_box_gui.see("end")
        except:
            pass

# -------------------------------
# FUNGSI ABSENSI - DENGAN LOGGING DETAIL
# -------------------------------
def proses_absensi(kode, status_absen):
    """
    Proses logika absensi dan simpan ke DB.
    Menggunakan schema baru: employees & attendance_logs.
    """
    if not MYSQL_AVAILABLE:
        msg = "Database tidak tersedia"
        logging.error(msg)
        return {"status": "error", "message": msg}
    
    # Convert status dari GUI/API format ke database enum format
    status_mapping = {
        "Masuk": "masuk",
        "Pulang": "pulang",
        "Lembur": "lembur",
        "Pulang Lembur": "pulang_lembur"
    }
    
    status_db = status_mapping.get(status_absen)
    if not status_db:
        msg = f"Status tidak valid: {status_absen}"
        logging.error(f"[DEBUG] {msg}")
        return {"status": "error", "message": msg}
    
    logging.info(f"[DEBUG] Memproses absensi: Kode={kode}, Status={status_absen} -> {status_db}")
    
    try:
        with get_db_connection() as db:
            if db is None:
                return {"status": "error", "message": "Koneksi database gagal"}
            
            cursor = db.cursor(dictionary=True)

            # Cek apakah karyawan terdaftar dan aktif
            cursor.execute(
                "SELECT id, name, is_active FROM employees WHERE code = %s",
                (kode,)
            )
            employee = cursor.fetchone()
            logging.info(f"[DEBUG] Hasil query employee: {employee}")
            
            if not employee:
                msg = "Kode karyawan tidak ditemukan"
                logging.warning(f"[DEBUG] {msg}")
                cursor.close()
                return {"status": "error", "message": msg}
            
            if not employee["is_active"]:
                msg = f"Karyawan {employee['name']} tidak aktif"
                logging.warning(f"[DEBUG] {msg}")
                cursor.close()
                return {"status": "error", "message": msg}

            employee_id = employee["id"]
            nama = employee["name"]
            sekarang = datetime.now()
            hari_ini = date.today()
            logging.info(f"[DEBUG] Employee ditemukan: ID={employee_id}, Nama={nama}")

            # Cek log terakhir hari ini
            cursor.execute("""
                SELECT status, event_time 
                FROM attendance_logs
                WHERE employee_id = %s AND DATE(event_time) = %s
                ORDER BY event_time DESC LIMIT 1
            """, (employee_id, hari_ini))
            last_log = cursor.fetchone()
            logging.info(f"[DEBUG] Log terakhir hari ini: {last_log}")

            # Logika validasi absensi berdasarkan status terakhir
            if status_db == "masuk":
                if last_log and last_log["status"] == "masuk":
                    msg = f"{nama} sudah absen masuk hari ini."
                    logging.info(f"[DEBUG] {msg}")
                    cursor.close()
                    return {"status": "info", "message": msg}
                    
            elif status_db == "pulang":
                if not last_log:
                    msg = f"{nama} belum absen masuk."
                    logging.info(f"[DEBUG] {msg}")
                    cursor.close()
                    return {"status": "info", "message": msg}
                if last_log["status"] not in ("masuk", "lembur"):
                    msg = f"{nama} belum absen masuk atau lembur."
                    logging.info(f"[DEBUG] {msg}")
                    cursor.close()
                    return {"status": "info", "message": msg}
                if last_log["status"] == "pulang":
                    msg = f"{nama} sudah absen pulang hari ini."
                    logging.info(f"[DEBUG] {msg}")
                    cursor.close()
                    return {"status": "info", "message": msg}
                    
            elif status_db == "lembur":
                if not last_log or last_log["status"] != "pulang":
                    msg = f"{nama} harus absen pulang dulu sebelum lembur."
                    logging.info(f"[DEBUG] {msg}")
                    cursor.close()
                    return {"status": "info", "message": msg}
                    
            elif status_db == "pulang_lembur":
                if not last_log or last_log["status"] != "lembur":
                    msg = f"{nama} belum absen lembur."
                    logging.info(f"[DEBUG] {msg}")
                    cursor.close()
                    return {"status": "info", "message": msg}

            # Simpan log absensi ke tabel baru
            cursor.execute(
                "INSERT INTO attendance_logs (employee_id, status, event_time) VALUES (%s, %s, %s)",
                (employee_id, status_db, sekarang)
            )
            db.commit()
            cursor.close()

            msg = f"Absensi {status_absen} berhasil!"
            logging.info(f"[DEBUG] {msg}")
            return {
                "status": "success",
                "message": msg,
                "nama": nama,
                "waktu": sekarang.strftime("%Y-%m-%d %H:%M:%S")
            }
    except Exception as e:
        logging.error(f"[DEBUG] Error absensi: {e}")
        return {"status": "error", "message": f"Gagal proses absensi: {str(e)}"}

def absen_dan_buka_pintu(kode, status):
    """
    Fungsi utama untuk absensi dan trigger doorlock otomatis.
    Ditambahkan logging untuk debugging.
    """
    logging.info(f"[DEBUG] Memanggil absen_dan_buka_pintu: Kode={kode}, Status={status}")
    result = proses_absensi(kode, status)
    logging.info(f"[DEBUG] Hasil proses_absensi: {result}")
    
    if result["status"] == "success":
        msg = f"Absensi {status} berhasil untuk {result['nama']}. Membuka pintu otomatis..."
        logging.info(f"[DEBUG] {msg}")
        log_message(msg)
        # Jika absensi sukses, buka pintu otomatis
        threading.Thread(target=open_door, daemon=True).start()
    elif result["status"] == "info":
        msg = f"Absensi {status} info: {result['message']}"
        logging.info(f"[DEBUG] {msg}")
        log_message(msg)
    else:  # error
        msg = f"Absensi {status} gagal: {result['message']}"
        logging.error(f"[DEBUG] {msg}")
        log_message(msg)
    
    return result

# -------------------------------
# FLASK API ROUTES
# -------------------------------
@app_flask.route('/')
def home():
    return jsonify({
        "message": "Sistem Absensi + Doorlock IGASAR aktif",
        "version": "3.0",
        "features": ["absensi", "doorlock", "gui"]
    })

@app_flask.route('/absen', methods=['POST'])
def api_absen():
    """
    API untuk proses absensi dan trigger doorlock otomatis.
    Ditambahkan logging untuk debugging.
    """
    data = request.get_json(force=True)
    logging.info(f"[DEBUG] Menerima request /absen: {data}")
    
    if data.get("token") != SECURE_TOKEN:
        msg = "Unauthorized"
        logging.warning(f"[DEBUG] {msg}")
        return jsonify({"status": "error", "message": msg}), 403

    kode = data.get("kode")
    status = data.get("status")
    
    if not kode or not status:
        msg = "Kode dan status wajib diisi"
        logging.warning(f"[DEBUG] {msg}")
        return jsonify({"status": "error", "message": msg}), 400

    result = absen_dan_buka_pintu(kode, status)
    logging.info(f"[DEBUG] Mengembalikan hasil API /absen: {result}")
    # Kembalikan hasil ke API caller
    return jsonify(result)

@app_flask.route('/door/open', methods=['POST'])
def api_open():
    """API untuk membuka pintu secara manual."""
    data = request.get_json(force=True)
    if data.get("token") != SECURE_TOKEN:
        return jsonify({"status": "error", "message": "Unauthorized"}), 403

    delay = data.get("delay", current_delay)
    msg = f"Pintu dibuka via API selama {delay} detik."
    logging.info(f"[DEBUG] {msg}")
    log_message(msg)
    threading.Thread(target=open_door, args=(delay,), daemon=True).start()
    return jsonify({"status": "success", "delay_used": delay})

@app_flask.route('/door/status', methods=['GET'])
def api_status():
    """API untuk cek status pintu."""
    global door_status
    with status_lock:  # Akses aman ke variabel global
        return jsonify({"status": door_status})

@app_flask.route('/health', methods=['GET'])
def health():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'server': 'doorlock_absensi_complete',
        'version': '3.0',
        'gpio_mode': 'REAL' if GPIO_AVAILABLE else 'SIMULATED',
        'mysql': 'AVAILABLE' if MYSQL_AVAILABLE else 'DISABLED',
        'gui': 'AVAILABLE' if TKINTER_AVAILABLE else 'DISABLED'
    })

# -------------------------------
# THREAD SERVER FLASK
# -------------------------------
def start_flask():
    logging.info("Menjalankan API Flask pada port 5000...")
    app_flask.run(host="0.0.0.0", port=5000, debug=False, use_reloader=False)

# -------------------------------
# TKINTER GUI
# -------------------------------
def setup_gui():
    if not TKINTER_AVAILABLE:
        logging.error("Tkinter tidak tersedia, GUI tidak dapat dijalankan")
        return
    
    global status_label_gui, log_box_gui

    root = tk.Tk()
    root.title("Sistem Absensi + Doorlock IGASAR")
    root.geometry("700x500")
    root.configure(bg="#f4f6f9")

    # --- Bagian Absensi ---
    frame_absen = tk.Frame(root, bg="#f4f6f9")
    frame_absen.pack(pady=10)

    tk.Label(frame_absen, text="Masukkan Kode Karyawan:", font=("Arial", 12), bg="#f4f6f9").grid(row=0, column=0, padx=10, pady=10)
    entry_kode = tk.Entry(frame_absen, font=("Arial", 12))
    entry_kode.grid(row=0, column=1, padx=10, pady=10)
    entry_kode.focus()

    def absen_handler(status):
        kode = entry_kode.get().strip()
        if not kode:
            messagebox.showwarning("Peringatan", "Kode karyawan tidak boleh kosong!")
            root.focus_force()
            return

        result = absen_dan_buka_pintu(kode, status)
        
        if result["status"] == "success":
            messagebox.showinfo("Berhasil", result["message"])
            entry_kode.delete(0, tk.END)
            entry_kode.focus()
        elif result["status"] == "info":
            messagebox.showinfo("Info", result["message"])
        else:  # error
            messagebox.showerror("Gagal", result["message"])
        
        root.focus_force()

    # Tombol absensi
    frame_btn = tk.Frame(root, bg="#f4f6f9")
    frame_btn.pack()

    buttons = [
        ("Masuk", "Masuk"),
        ("Pulang", "Pulang"),
        ("Lembur", "Lembur"),
        ("Pulang Lembur", "Pulang Lembur")
    ]

    for idx, (label_text, status) in enumerate(buttons):
        tk.Button(frame_btn, text=label_text, width=15, height=2,
                  command=lambda s=status: absen_handler(s)).grid(row=0, column=idx, padx=5, pady=5)

    # --- Bagian Doorlock ---
    door_frame = tk.LabelFrame(root, text="Doorlock Control", bg="#f4f6f9")
    door_frame.pack(pady=10, fill="x", padx=20)

    # Label status doorlock
    tk.Label(door_frame, text="Status Doorlock:", font=("Arial", 14), bg="#f4f6f9").pack(pady=(5, 0))
    status_label_gui = tk.Label(door_frame, text="TERKUNCI", font=("Arial", 20, "bold"), fg="red", bg="#f4f6f9")
    status_label_gui.pack(pady=5)

    # Frame untuk delay
    delay_frame = ttk.Frame(door_frame)
    delay_frame.pack(pady=5)

    delay_var = tk.IntVar(value=current_delay)
    tk.Label(delay_frame, text="Delay Buka (detik):", bg="#f4f6f9").pack(side="left", padx=(0, 5))
    delay_entry = ttk.Entry(delay_frame, width=5, textvariable=delay_var)
    delay_entry.pack(side="left", padx=(0, 5))

    def apply_delay():
        global current_delay
        try:
            val = int(delay_var.get())
            if val <= 0:
                raise ValueError
            current_delay = val
            log_message(f"Delay diatur menjadi {current_delay} detik via GUI.")
        except ValueError:
            messagebox.showerror("Input Salah", "Masukkan angka valid (>0)!")

    ttk.Button(delay_frame, text="Terapkan", command=apply_delay).pack(side="left")

    # Frame untuk tombol manual
    btn_frame = ttk.Frame(door_frame)
    btn_frame.pack(pady=5)

    def buka_manual():
        log_message("Pintu dibuka manual via GUI.")
        threading.Thread(target=open_door, daemon=True).start()

    def kunci_manual():
        global door_status
        with status_lock:
            set_relay(False)
            door_status = "Terkunci"
        update_status_label()
        log_message("Pintu dikunci manual via GUI.")

    tk.Button(btn_frame, text="🔓 Buka Pintu", width=12, command=buka_manual, bg="#90EE90").pack(side="left", padx=5)
    tk.Button(btn_frame, text="🔒 Kunci Manual", width=12, command=kunci_manual, bg="#FFB6C1").pack(side="left", padx=5)

    # --- Log Aktivitas ---
    log_frame = ttk.LabelFrame(root, text="Log Aktivitas")
    log_frame.pack(fill="both", expand=True, padx=20, pady=10)
    log_box_gui = tk.Text(log_frame, height=10)
    log_box_gui.pack(fill="both", expand=True)

    # Tambahkan log awal
    log_message("Sistem Absensi + Doorlock IGASAR dimulai.")

    # Tombol keluar
    exit_frame = ttk.Frame(root)
    exit_frame.pack(pady=5)

    def on_exit():
        if messagebox.askokcancel("Keluar", "Tutup program dan matikan doorlock?"):
            with status_lock:
                set_relay(False)  # Pastikan pintu terkunci
            GPIO.cleanup()
            root.destroy()

    tk.Button(exit_frame, text="Keluar", command=on_exit, bg="#FF6347", fg="white").pack()

    # Update status awal
    update_status_label()

    root.protocol("WM_DELETE_WINDOW", on_exit)
    root.mainloop()

# -------------------------------
# MAIN
# -------------------------------
if __name__ == "__main__":
    print("=" * 60)
    print("SISTEM ABSENSI + DOORLOCK IGASAR - VERSION 3.0")
    print("=" * 60)
    print(f"GPIO Mode: {'REAL HARDWARE' if GPIO_AVAILABLE else 'SIMULATED'}")
    print(f"MySQL: {'CONNECTED' if MYSQL_AVAILABLE else 'DISABLED'}")
    print(f"GUI: {'ENABLED' if TKINTER_AVAILABLE else 'DISABLED'}")
    print(f"Token: {SECURE_TOKEN}")
    print(f"Relay Pin: GPIO {RELAY_PIN}")
    print(f"Default Delay: {DEFAULT_DELAY} detik")
    print("-" * 60)
    
    try:
        init_db()
        # Jalankan server Flask di thread terpisah
        flask_thread = threading.Thread(target=start_flask, daemon=True)
        flask_thread.start()
        
        # Jalankan GUI Tkinter di thread utama (jika tersedia)
        if TKINTER_AVAILABLE:
            setup_gui()
        else:
            logging.info("GUI tidak tersedia, server berjalan dalam mode API only")
            logging.info("Tekan Ctrl+C untuk keluar")
            while True:
                time.sleep(1)
                
    except KeyboardInterrupt:
        logging.info("Menutup sistem...")
    finally:
        with status_lock:
            set_relay(False)
        GPIO.cleanup()
        logging.info("GPIO dibersihkan.")
