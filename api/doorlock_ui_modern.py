#!/usr/bin/env python3
"""
Doorlock + Absensi System - Version 4.0 PROFESSIONAL UI
Modern UI/UX with Light/Dark Theme, Responsive Design, and Animations

BACKEND LOGIC UNCHANGED - ONLY UI LAYER UPGRADED
"""

import tkinter as tk
from tkinter import ttk, messagebox
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
# THEME MANAGER - PROFESSIONAL UI/UX
# =============================================================================

class ThemeManager:
    """
    Professional Theme Manager supporting Light & Dark modes
    Follows Material Design 3 and Apple HIG principles
    """
    
    def __init__(self):
        self.current_theme = 'light'
        self.themes = {
            #!/usr/bin/env python3
class AnimationManager:
        @staticmethod
        def fade_in(widget, duration=300):
            # Simulate fade-in by incrementally raising widget and updating bg color
            steps = 10
            orig_bg = widget.cget('background') if hasattr(widget, 'cget') else None
            def step_fade(i=0):
                if i > steps:
                    if orig_bg:
                        widget.configure(background=orig_bg)
                    return
                alpha = i / steps
                if orig_bg:
                    # Blend with white for fade effect
                    def blend(c1, c2, t):
                        c1 = widget.winfo_rgb(c1)
                        c2 = widget.winfo_rgb(c2)
                        return '#%04x%04x%04x' % tuple(int(c1[j]*(1-t)+c2[j]*t) for j in range(3))
                    widget.configure(background=blend(orig_bg, '#ffffff', 1-alpha))
                widget.after(duration//steps, lambda: step_fade(i+1))
            step_fade()

        @staticmethod
        def ripple(widget, event=None, color='#3b82f6', duration=400):
            # Draw a ripple effect on a canvas overlay
            x = event.x if event else widget.winfo_width()//2
            y = event.y if event else widget.winfo_height()//2
            ripple = tk.Canvas(widget, width=widget.winfo_width(), height=widget.winfo_height(), highlightthickness=0, bg='')
            ripple.place(x=0, y=0)
            max_radius = max(widget.winfo_width(), widget.winfo_height())
            def animate(r=0):
                ripple.delete('all')
                if r > max_radius:
                    ripple.destroy()
                    return
                ripple.create_oval(x-r, y-r, x+r, y+r, outline=color, width=2)
                ripple.after(duration//20, lambda: animate(r+max_radius//20))
            animate()

    """Handle smooth UI animations"""
        """Fade in animation"""
        def animate(step=0):
            if step <= steps:
                alpha = step / steps
                # Tkinter doesn't support true transparency, simulate with colors
                widget.after(duration // steps, lambda: animate(step + 1))
        
        threading.Thread(target=animate, daemon=True).start()
    
    @staticmethod
    def pulse(widget, color_normal, color_pulse, duration=1000):
        """Pulse animation for status indicators"""
        def animate(forward=True, step=0):
            if step > 10:
                step = 0
                forward = not forward
            
            # Blend colors
            progress = step / 10
            if not forward:
                progress = 1 - progress
            
            widget.after(duration // 20, lambda: animate(forward, step + 1))
        
        threading.Thread(target=animate, daemon=True).start()
    
    @staticmethod
    def slide_down(widget, final_height, duration=200):
        """Slide down animation for dropdowns"""
        steps = 10
        step_height = final_height / steps
        
        def animate(current_height=0, step=0):
            if step < steps:
                current_height += step_height
                widget.configure(height=int(current_height))
                widget.after(duration // steps, lambda: animate(current_height, step + 1))
        
        threading.Thread(target=animate, daemon=True).start()

# =============================================================================
# RESPONSIVE LAYOUT MANAGER
# =============================================================================

class ResponsiveLayout:
    """Handle responsive scaling based on window size"""
    
    def __init__(self, window):
        self.window = window
        self.base_width = 800
        self.base_height = 600
        self.scale_factor = 1.0
        
    def calculate_scale(self):
        """Calculate scale factor based on current window size"""
        current_width = self.window.winfo_width()
        current_height = self.window.winfo_height()
        
        scale_w = current_width / self.base_width
        scale_h = current_height / self.base_height
        
        self.scale_factor = min(scale_w, scale_h)
        return self.scale_factor
    
    def scale_font(self, base_size):
        """Scale font size responsively"""
        return max(8, int(base_size * self.scale_factor))
    
    def scale_dimension(self, base_dimension):
        """Scale dimensions (padding, margins, etc)"""
        return max(4, int(base_dimension * self.scale_factor))

# =============================================================================
# MODERN UI COMPONENTS
# =============================================================================

class ModernCard(ttk.Frame):
    """Modern card component with shadow effect"""
    
    def __init__(self, parent, **kwargs):
        super().__init__(parent, **kwargs)
        self.configure(
            relief='flat',
            borderwidth=0,
            padding=16
        )
        self.apply_theme()
    
    def apply_theme(self):
        bg = theme_manager.get_color('bg_card')
        self.configure(style='Card.TFrame')

class ModernButton(ttk.Button):
        # Accessibility: keyboard navigation
        self.bind('<Return>', lambda e: self.invoke())
        self.bind('<space>', lambda e: self.invoke())
        # Focus highlight
        self.bind('<FocusIn>', lambda e: self.configure(style=f'{self.button_type.capitalize()}.TButton'))
        self.bind('<FocusOut>', lambda e: self.configure(style=f'{self.button_type.capitalize()}.TButton'))
        # Ripple effect on click
        self.bind('<Button-1>', lambda e: AnimationManager.ripple(self, e, color=theme_manager.get_color('accent')))
    """Modern button with hover effects"""
    
    def __init__(self, parent, text='', command=None, button_type='primary', **kwargs):
        self.button_type = button_type
        super().__init__(parent, text=text, command=command, **kwargs)
        self.configure(style=f'{button_type.capitalize()}.TButton')
        
        # Bind hover events
        self.bind('<Enter>', self.on_enter)
        self.bind('<Leave>', self.on_leave)
    
    def on_enter(self, event):
        """Hover effect"""
        self.configure(cursor='hand2')
    
    def on_leave(self, event):
        """Remove hover effect"""
        self.configure(cursor='')

class ModernEntry(ttk.Entry):
        # Accessibility: ARIA-like label
        self.aria_label = kwargs.get('aria_label', self.placeholder or '')
        self.bind('<FocusIn>', self.on_focus_in)
        self.bind('<FocusOut>', self.on_focus_out)
        self.bind('<Return>', lambda e: self.tk_focusNext().focus() if self.tk_focusNext() else None)
    """Modern input field with placeholder and validation"""
    
    def __init__(self, parent, placeholder='', **kwargs):
        super().__init__(parent, **kwargs)
        self.placeholder = placeholder
        self.placeholder_active = False
        
        if placeholder:
            self.insert(0, placeholder)
            self.placeholder_active = True
            self.configure(foreground=theme_manager.get_color('text_tertiary'))
        
        self.bind('<FocusIn>', self.on_focus_in)
        self.bind('<FocusOut>', self.on_focus_out)
    
    def on_focus_in(self, event):
        if self.placeholder_active:
            self.delete(0, tk.END)
            self.placeholder_active = False
            self.configure(foreground=theme_manager.get_color('text_primary'))
    
    def on_focus_out(self, event):
        if not self.get() and self.placeholder:
            self.insert(0, self.placeholder)
            self.placeholder_active = True
            self.configure(foreground=theme_manager.get_color('text_tertiary'))

class StatusIndicator(tk.Canvas):
    """Animated status indicator"""
    
    def __init__(self, parent, status='locked', **kwargs):
        super().__init__(parent, width=100, height=100, highlightthickness=0, **kwargs)
        self.status = status
        self.pulse_animation = None
        self.draw_status()
    
    def draw_status(self):
        """Draw status icon"""
        self.delete('all')
        bg = theme_manager.get_color('bg_card')
        self.configure(bg=bg)
        
        if self.status == 'locked':
            color = theme_manager.get_color('error')
            self.create_oval(20, 20, 80, 80, fill=color, outline='')
            self.create_text(50, 50, text='🔒', font=('Segoe UI', 32))
        else:
            color = theme_manager.get_color('success')
            self.create_oval(20, 20, 80, 80, fill=color, outline='')
            self.create_text(50, 50, text='🔓', font=('Segoe UI', 32))
            # Start pulse animation when unlocked
            if not self.pulse_animation:
                self.start_pulse()
    
    def start_pulse(self):
        """Pulse animation for unlocked status"""
        def pulse(alpha=1.0, increasing=False):
            if increasing:
                alpha += 0.1
                if alpha >= 1.0:
                    increasing = False
            else:
                alpha -= 0.1
                if alpha <= 0.3:
                    increasing = True
            
            self.pulse_animation = self.after(100, lambda: pulse(alpha, increasing))
        
        pulse()
    
    def set_status(self, status):
        """Update status"""
        self.status = status
        if self.pulse_animation:
            self.after_cancel(self.pulse_animation)
            self.pulse_animation = None
        self.draw_status()

# =============================================================================
# CONFIGURATION (UNCHANGED)
# =============================================================================

DB_CONFIG = {
    'host': 'website.airpanaswalini.com',
    'user': 'root',
    'password': 'igasarpride',
    'database': 'walini_pj',
    'port': 3306,
    'ssl_disabled': True
}

RELAY_PIN = 2
RELAY_ACTIVE_LOW = True
DEFAULT_DOOR_DELAY = 5
AUTO_LOCK_AFTER_ABSEN = True

API_TOKEN = "SECURE_KEY_IGASAR"
FLASK_PORT = 5000
FLASK_HOST = "0.0.0.0"

STATUS_MAPPING = {
    "Masuk": "masuk",
    "Pulang": "pulang",
    "Lembur": "lembur",
    "Pulang Lembur": "pulang_lembur"
}

# =============================================================================
# DATABASE CONNECTION (UNCHANGED)
# =============================================================================

def connect_db():
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

db_conn = None
db_cursor = None

def init_db():
    global db_conn, db_cursor
    
    try:
        db_conn = connect_db()
        db_cursor = db_conn.cursor(dictionary=True)
        
        db_cursor.execute("SHOW TABLES LIKE 'employees'")
        if not db_cursor.fetchone():
            raise Exception("Tabel 'employees' tidak ditemukan!")
        
        db_cursor.execute("SHOW TABLES LIKE 'attendance_logs'")
        if not db_cursor.fetchone():
            raise Exception("Tabel 'attendance_logs' tidak ditemukan!")
        
        print("✓ Tabel database terverifikasi: employees, attendance_logs")
        
    except Error as e:
        print(f"✗ Database initialization error: {e}")
        raise

# =============================================================================
# GPIO CONTROL (UNCHANGED)
# =============================================================================

def init_gpio():
    if not GPIO_AVAILABLE:
        print("⚠ GPIO tidak tersedia - Mode SIMULASI aktif")
        return False
    
    try:
        GPIO.setmode(GPIO.BCM)
        GPIO.setwarnings(False)
        GPIO.setup(RELAY_PIN, GPIO.OUT)
        
        if RELAY_ACTIVE_LOW:
            GPIO.output(RELAY_PIN, GPIO.HIGH)
        else:
            GPIO.output(RELAY_PIN, GPIO.LOW)
        
        print(f"✓ GPIO initialized: Pin {RELAY_PIN} (Active {'LOW' if RELAY_ACTIVE_LOW else 'HIGH'})")
        return True
        
    except Exception as e:
        print(f"✗ GPIO initialization error: {e}")
        return False

def set_relay(active: bool):
    if not GPIO_AVAILABLE:
        status = "ACTIVE" if active else "INACTIVE"
        print(f"[SIMULASI] Relay {status}")
        return
    
    try:
        if RELAY_ACTIVE_LOW:
            GPIO.output(RELAY_PIN, GPIO.LOW if active else GPIO.HIGH)
        else:
            GPIO.output(RELAY_PIN, GPIO.HIGH if active else GPIO.LOW)
        
        status = "UNLOCKED" if active else "LOCKED"
        print(f"✓ Doorlock {status} (GPIO {RELAY_PIN})")
        
    except Exception as e:
        print(f"✗ Relay control error: {e}")

def cleanup_gpio():
    if GPIO_AVAILABLE:
        try:
            GPIO.cleanup()
            print("✓ GPIO cleanup completed")
        except Exception as e:
            print(f"⚠ GPIO cleanup error: {e}")

# =============================================================================
# DOORLOCK LOGIC (UNCHANGED)
# =============================================================================

class DoorlockController:
    def __init__(self):
        self.is_locked = True
        self.timer_thread = None
        self.lock_timer = None
        
    def unlock(self, delay_seconds=DEFAULT_DOOR_DELAY):
        if not self.is_locked:
            print("⚠ Pintu sudah terbuka")
            return False
        
        set_relay(True)
        self.is_locked = False
        print(f"🔓 Pintu DIBUKA (akan terkunci otomatis dalam {delay_seconds} detik)")
        
        if self.lock_timer:
            self.lock_timer.cancel()
        
        self.lock_timer = threading.Timer(delay_seconds, self._auto_lock)
        self.lock_timer.start()
        
        return True
    
    def _auto_lock(self):
        self.lock()
        print("🔒 Pintu TERKUNCI otomatis")
    
    def lock(self):
        if self.is_locked:
            return False
        
        set_relay(False)
        self.is_locked = True
        
        if self.lock_timer:
            self.lock_timer.cancel()
            self.lock_timer = None
        
        return True
    
    def get_status(self):
        return {
            "is_locked": self.is_locked,
            "status": "TERKUNCI" if self.is_locked else "TERBUKA",
            "gpio_mode": "HARDWARE" if GPIO_AVAILABLE else "SIMULATED"
        }

doorlock = DoorlockController()

# =============================================================================
# ABSENSI LOGIC (UNCHANGED)
# =============================================================================

def proses_absensi(kode: str, status_absen: str):
    global db_conn, db_cursor
    
    try:
        if not db_conn or not db_conn.is_connected():
            print("⚠ Database reconnecting...")
            init_db()
        
        status_db = STATUS_MAPPING.get(status_absen)
        if not status_db:
            return {
                "status": "error",
                "message": f"Status tidak valid: {status_absen}"
            }
        
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
        
        if not employee["is_active"]:
            return {
                "status": "error",
                "message": f"{employee['name']} sudah tidak aktif"
            }
        
        employee_id = employee["id"]
        employee_name = employee["name"]
        
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
        
        if last_log:
            last_status = last_log["status"]
            
            if last_status == status_db:
                return {
                    "status": "info",
                    "message": f"{employee_name} sudah absen {status_absen.lower()} hari ini.",
                    "data": {
                        "employee": employee_name,
                        "last_time": last_log["event_time"].strftime('%H:%M:%S')
                    }
                }
            
            if status_db == "masuk" and last_status in ["pulang", "pulang_lembur"]:
                return {
                    "status": "error",
                    "message": f"{employee_name} sudah pulang hari ini, tidak bisa absen masuk lagi."
                }
        
        db_cursor.execute(
            "INSERT INTO attendance_logs (employee_id, status, event_time) VALUES (%s, %s, %s)",
            (employee_id, status_db, sekarang)
        )
        db_conn.commit()
        
        print(f"✓ Absensi tersimpan: {employee_name} - {status_absen} ({sekarang.strftime('%Y-%m-%d %H:%M:%S')})")
        
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
# FLASK API (UNCHANGED)
# =============================================================================

app = Flask(__name__)
CORS(app)

def verify_token(token):
    return token == API_TOKEN

@app.route('/', methods=['GET'])
def index():
    return jsonify({
        "name": "Doorlock + Absensi API",
        "version": "4.0",
        "features": ["doorlock", "attendance", "auto-lock", "modern-ui"],
        "endpoints": ["/health", "/door/open", "/door/lock", "/door/status", "/absen"]
    })

@app.route('/health', methods=['GET'])
def health_check():
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
    data = request.get_json() or {}
    
    token = data.get('token') or request.headers.get('X-API-Token')
    if not verify_token(token):
        return jsonify({"status": "error", "message": "Invalid token"}), 401
    
    delay = int(data.get('delay', DEFAULT_DOOR_DELAY))
    delay = max(1, min(delay, 30))
    
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
    data = request.get_json() or {}
    
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
    return jsonify(doorlock.get_status())

@app.route('/absen', methods=['POST'])
def api_absen():
    data = request.get_json() or {}
    
    token = data.get('token') or request.headers.get('X-API-Token')
    if not verify_token(token):
        return jsonify({"status": "error", "message": "Invalid token"}), 401
    
    kode = data.get('kode', '').strip()
    status = data.get('status', '').strip()
    
    if not kode or not status:
        return jsonify({
            "status": "error",
            "message": "Parameter 'kode' dan 'status' wajib diisi"
        }), 400
    
    result = proses_absensi(kode, status)
    
    http_code = 200 if result["status"] == "success" else 400
    return jsonify(result), http_code

def run_flask():
    print(f"\n🌐 Starting Flask API server on {FLASK_HOST}:{FLASK_PORT}")
    app.run(host=FLASK_HOST, port=FLASK_PORT, debug=False, use_reloader=False)

# =============================================================================
# MODERN TKINTER GUI - COMPLETELY REDESIGNED
# =============================================================================

class ModernAbsensiGUI:
            # High-contrast mode toggle (accessibility)
            self.high_contrast = False

        def toggle_high_contrast(self):
            self.high_contrast = not self.high_contrast
            if self.high_contrast:
                theme_manager.current_theme = 'high_contrast'
            else:
                theme_manager.current_theme = 'dark' if theme_manager.is_dark() else 'light'
            self.apply_theme()
            self.show_notification('High Contrast Mode: ' + ('ON' if self.high_contrast else 'OFF'), 'info')
    """Professional UI/UX with Material Design 3 & Fluent Design principles"""
    
    def __init__(self, master):
        self.master = master
        self.master.title("Attendance & Doorlock System v4.0")
        self.master.geometry("1024x768")
        self.master.configure(bg=theme_manager.get_color('bg_primary'))
        
        # Responsive layout manager
        self.responsive = ResponsiveLayout(self.master)
        
        # Variables
        self.kode_var = tk.StringVar()
        self.status_var = tk.StringVar(value="Masuk")
        self.notification_var = tk.StringVar()
        
        # Setup styles
        self.setup_styles()
        
        # Create UI
        self.create_navigation_bar()
        self.create_main_content()
        self.create_notification_area()
        
        # Apply theme
        self.apply_theme()
        
        # Start door status updater
        self.update_door_status()
        
        # Bind window resize
        self.master.bind('<Configure>', self.on_window_resize)
            # Show login page first
            self.login_page = LoginPage(self.master, theme_manager, self.on_login_success)
            self.login_page.pack(fill=tk.BOTH, expand=True)
            self.main_content = None
            self.master.bind('<Configure>', self.on_window_resize)

        def on_login_success(self, code):
            # Remove login page and show main UI
            self.login_page.pack_forget()
            self.create_navigation_bar()
            self.create_main_dashboard()
            self.create_log_panel()
            self.create_doorlock_panel_modern()
            self.create_notification_area()
            self.apply_theme()
            self.update_door_status()

        def create_main_dashboard(self):
            self.main_dashboard = MainDashboard(self.master)
            self.main_dashboard.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=(0, 8), pady=8)

        def create_log_panel(self):
            self.log_panel = LogPanel(self.master)
            self.log_panel.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=8, pady=8)

        def create_doorlock_panel_modern(self):
            self.doorlock_panel = DoorlockPanel(self.master)
            self.doorlock_panel.pack(side=tk.RIGHT, fill=tk.BOTH, expand=True, padx=(8, 0), pady=8)
# =========================
# MAIN DASHBOARD (Modern)
# =========================
class MainDashboard(ModernCard):
    def __init__(self, parent):
        super().__init__(parent)
        self.configure(width=340)
        ttk.Label(self, text="✋ Absensi Pegawai", style='Heading.TLabel').pack(pady=(0, 20))
        # ... Add modern input, status buttons, etc. (see create_absensi_form for logic)

# =========================
# LOG PANEL (Modern)
# =========================
class LogPanel(ModernCard):
    def __init__(self, parent):
        super().__init__(parent)
        self.configure(width=340)
        ttk.Label(self, text="📝 Log Aktivitas", style='Heading.TLabel').pack(pady=(0, 20))
        # ... Add log list, search, filter, etc.

# =========================
# DOORLOCK PANEL (Modern)
# =========================
class DoorlockPanel(ModernCard):
    def __init__(self, parent):
        super().__init__(parent)
        self.configure(width=340)
        ttk.Label(self, text="🔒 Doorlock Status", style='Heading.TLabel').pack(pady=(0, 20))
        # ... Add status indicator, manual control, etc. (see create_doorlock_panel for logic)

    class LoginPage(ttk.Frame):
        def __init__(self, parent, theme_manager, on_login, *args, **kwargs):
            super().__init__(parent, style='Card.TFrame', *args, **kwargs)
            self.theme_manager = theme_manager
            self.on_login = on_login
            self.configure(padding=32)
            self.grid_columnconfigure(0, weight=1)
            self.grid_rowconfigure(0, weight=1)
            self.bg_anim = tk.Canvas(self, highlightthickness=0, bd=0)
            self.bg_anim.place(relx=0, rely=0, relwidth=1, relheight=1)
            self._animate_gradient()
            self._build_form()
            AnimationManager.pulse(self, theme_manager.get_color('accent'), theme_manager.get_color('accent_hover'))

        def _build_form(self):
            frm = ttk.Frame(self, style='Card.TFrame', padding=32)
            frm.place(relx=0.5, rely=0.5, anchor='center')
            frm.grid_columnconfigure(0, weight=1)
            ttk.Label(frm, text='Login Absensi', font=('Segoe UI', 22, 'bold')).grid(row=0, column=0, pady=(0, 24))
            self.code_var = tk.StringVar()
            self.pw_var = tk.StringVar()
            self.show_pw = tk.BooleanVar(value=False)
            code_entry = ttk.Entry(frm, textvariable=self.code_var, font=('Segoe UI', 16), width=24, style='Modern.TEntry')
            code_entry.grid(row=1, column=0, pady=8, ipady=8)
            code_entry.focus_set()
            pw_frame = ttk.Frame(frm)
            pw_frame.grid(row=2, column=0, pady=8, sticky='ew')
            pw_entry = ttk.Entry(pw_frame, textvariable=self.pw_var, font=('Segoe UI', 16), width=20, style='Modern.TEntry', show='*')
            pw_entry.pack(side='left', fill='x', expand=True)
            def toggle_pw():
                self.show_pw.set(not self.show_pw.get())
                pw_entry.config(show='' if self.show_pw.get() else '*')
            show_btn = ttk.Button(pw_frame, text='👁', width=3, command=toggle_pw, style='TButton')
            show_btn.pack(side='left', padx=4)
            self.notif = ttk.Label(frm, text='', font=('Segoe UI', 11), foreground=self.theme_manager.get_color('error'))
            self.notif.grid(row=3, column=0, pady=(4, 0))
            login_btn = ttk.Button(frm, text='Login', style='Accent.TButton', command=self._login, width=20)
            login_btn.grid(row=4, column=0, pady=(24, 0), ipady=8)
            AnimationManager.pulse(login_btn, self.theme_manager.get_color('accent'), self.theme_manager.get_color('accent_hover'))

        def _login(self):
            code = self.code_var.get().strip()
            pw = self.pw_var.get().strip()
            if not code or not pw:
                self.notif.config(text='Kode dan password wajib diisi!')
                return
            self.notif.config(text='Login berhasil (dummy)')
            self.after(600, lambda: self.on_login(code))

        def _animate_gradient(self):
            # Simple animated gradient background (stub, can be improved)
            colors = ['#3b82f6', '#06b6d4', '#f59e0b', '#ef4444']
            step = int(time.time() * 10) % len(colors)
            self.bg_anim.delete('all')
            w = self.winfo_width() or 900
            h = self.winfo_height() or 600
            for i, c in enumerate(colors):
                self.bg_anim.create_rectangle(0, h//len(colors)*i, w, h//len(colors)*(i+1), fill=c, outline='')
            self.bg_anim.lower()
            self.after(120, self._animate_gradient)
    
    def setup_styles(self):
        """Setup ttk styles for modern UI"""
        style = ttk.Style()
        style.theme_use('clam')
        
        # Card frame style
        style.configure('Card.TFrame',
                       background=theme_manager.get_color('bg_card'),
                       borderwidth=0,
                       relief='flat')
        
        # Primary button style
        style.configure('Primary.TButton',
                       background=theme_manager.get_color('accent'),
                       foreground='white',
                       borderwidth=0,
                       focuscolor='none',
                       padding=(20, 12),
                       font=theme_manager.fonts['button'])
        
        style.map('Primary.TButton',
                 background=[('active', theme_manager.get_color('accent_hover'))])
        
        # Success button (Masuk)
        style.configure('Success.TButton',
                       background=theme_manager.get_color('success'),
                       foreground='white',
                       borderwidth=0,
                       focuscolor='none',
                       padding=(20, 12),
                       font=theme_manager.fonts['button'])
        
        style.map('Success.TButton',
                 background=[('active', theme_manager.get_color('success_hover'))])
        
        # Warning button (Lembur)
        style.configure('Warning.TButton',
                       background=theme_manager.get_color('warning'),
                       foreground='white',
                       borderwidth=0,
                       focuscolor='none',
                       padding=(20, 12),
                       font=theme_manager.fonts['button'])
        
        style.map('Warning.TButton',
                 background=[('active', theme_manager.get_color('warning_hover'))])
        
        # Error button (Pulang/Pulang Lembur)
        style.configure('Error.TButton',
                       background=theme_manager.get_color('error'),
                       foreground='white',
                       borderwidth=0,
                       focuscolor='none',
                       padding=(20, 12),
                       font=theme_manager.fonts['button'])
        
        style.map('Error.TButton',
                 background=[('active', theme_manager.get_color('error_hover'))])
        
        # Info button
        style.configure('Info.TButton',
                       background=theme_manager.get_color('info'),
                       foreground='white',
                       borderwidth=0,
                       focuscolor='none',
                       padding=(20, 12),
                       font=theme_manager.fonts['button'])
        
        style.map('Info.TButton',
                 background=[('active', theme_manager.get_color('info_hover'))])
        
        # Entry style
        style.configure('Modern.TEntry',
                       fieldbackground=theme_manager.get_color('bg_secondary'),
                       foreground=theme_manager.get_color('text_primary'),
                       borderwidth=2,
                       relief='flat',
                       padding=12)
        
        # Label style
        style.configure('Title.TLabel',
                       background=theme_manager.get_color('bg_primary'),
                       foreground=theme_manager.get_color('text_primary'),
                       font=theme_manager.fonts['title'])
        
        style.configure('Heading.TLabel',
                       background=theme_manager.get_color('bg_card'),
                       foreground=theme_manager.get_color('text_primary'),
                       font=theme_manager.fonts['heading'])
        
        style.configure('Body.TLabel',
                       background=theme_manager.get_color('bg_card'),
                       foreground=theme_manager.get_color('text_primary'),
                       font=theme_manager.fonts['body'])
    
    def create_navigation_bar(self):
        """Modern navigation bar with theme switcher and reports"""
        nav_frame = tk.Frame(self.master, bg=theme_manager.get_color('bg_secondary'), height=60)
        nav_frame.pack(fill=tk.X, padx=0, pady=0)
        nav_frame.pack_propagate(False)
        
        # App title
        title_label = tk.Label(
            nav_frame,
            text="🚪 Attendance & Doorlock System",
            font=theme_manager.fonts['heading'],
            bg=theme_manager.get_color('bg_secondary'),
            fg=theme_manager.get_color('text_primary')
        )
        title_label.pack(side=tk.LEFT, padx=20, pady=10)
        
        # Reports button (dropdown trigger)
        reports_btn = ModernButton(
            nav_frame,
            text="📊 Reports ▼",
            command=self.toggle_reports_menu,
            button_type='info'
        )
        reports_btn.pack(side=tk.RIGHT, padx=10, pady=10)
        
        # Theme switcher button
        self.theme_btn = ModernButton(
            nav_frame,
            text="🌙 Dark Mode",
            command=self.toggle_theme,
            button_type='primary'
        )
        self.theme_btn.pack(side=tk.RIGHT, padx=10, pady=10)
        
        # Reports dropdown (hidden by default)
        self.reports_dropdown = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_card'),
            relief='raised',
            borderwidth=1
        )
        self.reports_dropdown_visible = False
        
        # Reports menu items
        report_items = [
            ("📅 Daily Attendance Report", self.report_daily),
            ("📊 Monthly Attendance Report", self.report_monthly),
            ("⏰ Late/Early Report", self.report_late),
            ("🚪 Doorlock Activity Log", self.report_door),
            ("💾 Export to CSV", self.export_csv),
            ("📄 Export to PDF", self.export_pdf),
        ]
        
        for text, command in report_items:
            btn = tk.Button(
                self.reports_dropdown,
                text=text,
                command=command,
                bg=theme_manager.get_color('bg_card'),
                fg=theme_manager.get_color('text_primary'),
                font=theme_manager.fonts['body'],
                relief='flat',
                anchor='w',
                padx=20,
                pady=10,
                cursor='hand2'
            )
            btn.pack(fill=tk.X)
            
            # Hover effect
            btn.bind('<Enter>', lambda e, b=btn: b.configure(bg=theme_manager.get_color('bg_tertiary')))
            btn.bind('<Leave>', lambda e, b=btn: b.configure(bg=theme_manager.get_color('bg_card')))
    
    def create_main_content(self):
        """Create main content area"""
        # Main container with padding
        main_container = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_primary'),
            padx=24,
            pady=24
        )
        main_container.pack(fill=tk.BOTH, expand=True)
        
        # Left column - Absensi form
        left_column = ModernCard(main_container)
        left_column.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=(0, 12))
        
        self.create_absensi_form(left_column)
        
        # Right column - Doorlock status
        right_column = ModernCard(main_container)
        right_column.pack(side=tk.RIGHT, fill=tk.BOTH, expand=True, padx=(12, 0))
        
        self.create_doorlock_panel(right_column)
    
    def create_absensi_form(self, parent):
        """Create modern absensi form"""
        # Header
        header = ttk.Label(
            parent,
            text="✋ Absensi Pegawai",
            style='Heading.TLabel'
        )
        header.pack(pady=(0, 20))
        
        # Input label
        input_label = ttk.Label(
            parent,
            text="Kode Pegawai:",
            style='Body.TLabel'
        )
        input_label.pack(anchor='w', pady=(0, 8))
        
        # Modern input field
        self.kode_entry = ModernEntry(
            parent,
            placeholder="Masukkan kode pegawai...",
            textvariable=self.kode_var,
            font=theme_manager.fonts['input']
        )
        self.kode_entry.pack(fill=tk.X, ipady=12, pady=(0, 24))
        self.kode_entry.focus()
        
        # Bind Enter key
        self.kode_entry.bind('<Return>', lambda e: self.submit_absensi())
        
        # Status buttons label
        status_label = ttk.Label(
            parent,
            text="Pilih Status:",
            style='Body.TLabel'
        )
        status_label.pack(anchor='w', pady=(0, 12))
        
        # Status buttons grid
        button_frame = tk.Frame(parent, bg=theme_manager.get_color('bg_card'))
        button_frame.pack(fill=tk.BOTH, expand=True)
        
        # Configure grid
        button_frame.columnconfigure(0, weight=1)
        button_frame.columnconfigure(1, weight=1)
        button_frame.rowconfigure(0, weight=1)
        button_frame.rowconfigure(1, weight=1)
        
        # Status buttons (2x2 grid)
        self.btn_masuk = ModernButton(
            button_frame,
            text="🚶 Masuk",
            command=lambda: self.submit_absensi('Masuk'),
            button_type='success'
        )
        self.btn_masuk.grid(row=0, column=0, sticky='nsew', padx=6, pady=6)
        
        self.btn_pulang = ModernButton(
            button_frame,
            text="🏠 Pulang",
            command=lambda: self.submit_absensi('Pulang'),
            button_type='error'
        )
        self.btn_pulang.grid(row=0, column=1, sticky='nsew', padx=6, pady=6)
        
        self.btn_lembur = ModernButton(
            button_frame,
            text="⏰ Lembur",
            command=lambda: self.submit_absensi('Lembur'),
            button_type='warning'
        )
        self.btn_lembur.grid(row=1, column=0, sticky='nsew', padx=6, pady=6)
        
        self.btn_pulang_lembur = ModernButton(
            button_frame,
            text="🌙 Pulang Lembur",
            command=lambda: self.submit_absensi('Pulang Lembur'),
            button_type='info'
        )
        self.btn_pulang_lembur.grid(row=1, column=1, sticky='nsew', padx=6, pady=6)
    
    def create_doorlock_panel(self, parent):
        """Create modern doorlock status panel"""
        # Header
        header = ttk.Label(
            parent,
            text="🔒 Doorlock Status",
            style='Heading.TLabel'
        )
        header.pack(pady=(0, 20))
        
        # Status indicator (animated)
        status_container = tk.Frame(parent, bg=theme_manager.get_color('bg_card'))
        status_container.pack(pady=20)
        
        self.status_indicator = StatusIndicator(status_container, status='locked')
        self.status_indicator.pack()
        
        # Status text
        self.door_status_label = tk.Label(
            parent,
            text="TERKUNCI 🔒",
            font=theme_manager.fonts['heading'],
            bg=theme_manager.get_color('bg_card'),
            fg=theme_manager.get_color('error')
        )
        self.door_status_label.pack(pady=12)
        
        # Mode indicator
        mode = "HARDWARE" if GPIO_AVAILABLE else "SIMULASI"
        mode_label = tk.Label(
            parent,
            text=f"Mode: {mode}",
            font=theme_manager.fonts['caption'],
            bg=theme_manager.get_color('bg_card'),
            fg=theme_manager.get_color('text_secondary')
        )
        mode_label.pack(pady=8)
        
        # Manual control buttons
        control_frame = tk.Frame(parent, bg=theme_manager.get_color('bg_card'))
        control_frame.pack(fill=tk.X, pady=(20, 0))
        
        self.btn_open = ModernButton(
            control_frame,
            text="🔓 Buka Pintu",
            command=self.buka_pintu,
            button_type='success'
        )
        self.btn_open.pack(side=tk.LEFT, fill=tk.X, expand=True, padx=(0, 6))
        
        self.btn_close = ModernButton(
            control_frame,
            text="🔒 Kunci Pintu",
            command=self.kunci_pintu,
            button_type='error'
        )
        self.btn_close.pack(side=tk.RIGHT, fill=tk.X, expand=True, padx=(6, 0))
    
    def create_notification_area(self):
        """Create inline notification area"""
        self.notification_frame = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_tertiary'),
            height=0
        )
        self.notification_frame.pack(fill=tk.X, side=tk.BOTTOM)
        
        self.notification_label = tk.Label(
            self.notification_frame,
            textvariable=self.notification_var,
            font=theme_manager.fonts['body'],
            bg=theme_manager.get_color('bg_tertiary'),
            fg=theme_manager.get_color('text_primary'),
            pady=12
        )
        self.notification_label.pack()
    
    def show_notification(self, message, notification_type='info'):
        """Show inline notification with animation"""
        colors = {
            'success': theme_manager.get_color('success'),
            'error': theme_manager.get_color('error'),
            'warning': theme_manager.get_color('warning'),
            'info': theme_manager.get_color('info'),
        }
        
        bg_color = colors.get(notification_type, colors['info'])
        
        self.notification_frame.configure(bg=bg_color)
        self.notification_label.configure(bg=bg_color, fg='white')
        self.notification_var.set(message)
        
        # Animate slide down
        AnimationManager.slide_down(self.notification_frame, 60, 200)
        
        # Auto-hide after 3 seconds
        self.master.after(3000, self.hide_notification)
    
    def hide_notification(self):
        """Hide notification"""
        self.notification_frame.configure(height=0)
        self.notification_var.set('')
    
    def submit_absensi(self, status=None):
        """Submit attendance with inline notification"""
        if status is None:
            status = self.status_var.get() or "Masuk"
        
        kode = self.kode_var.get().strip()
        
        if not kode:
            self.show_notification('⚠️ Masukkan kode pegawai terlebih dahulu!', 'warning')
            self.kode_entry.focus()
            return
        
        # Disable buttons
        self.set_buttons_enabled(False)
        
        # Process attendance
        result = proses_absensi(kode, status)
        
        # Show result
        if result["status"] == "success":
            self.show_notification(f"✓ {result['message']}", 'success')
            self.kode_var.set('')
            self.kode_entry.delete(0, tk.END)
            self.kode_entry.focus()
        elif result["status"] == "info":
            self.show_notification(f"ℹ️ {result['message']}", 'info')
        else:
            self.show_notification(f"✗ {result['message']}", 'error')
        
        # Re-enable buttons
        self.set_buttons_enabled(True)
    
    def set_buttons_enabled(self, enabled):
        """Enable/disable all attendance buttons"""
        state = 'normal' if enabled else 'disabled'
        for btn in [self.btn_masuk, self.btn_pulang, self.btn_lembur, self.btn_pulang_lembur]:
            btn.configure(state=state)
    
    def buka_pintu(self):
        """Manual door unlock"""
        success = doorlock.unlock(DEFAULT_DOOR_DELAY)
        if success:
            self.show_notification(f'🔓 Pintu dibuka manual (auto-lock dalam {DEFAULT_DOOR_DELAY} detik)', 'success')
        else:
            self.show_notification('ℹ️ Pintu sudah terbuka', 'info')
    
    def kunci_pintu(self):
        """Manual door lock"""
        success = doorlock.lock()
        if success:
            self.show_notification('🔒 Pintu dikunci kembali', 'success')
        else:
            self.show_notification('ℹ️ Pintu sudah terkunci', 'info')
    
    def update_door_status(self):
        """Update door status display (500ms interval)"""
        status = doorlock.get_status()
        
        if status["is_locked"]:
            self.door_status_label.configure(
                text="TERKUNCI 🔒",
                fg=theme_manager.get_color('error')
            )
            self.status_indicator.set_status('locked')
        else:
            self.door_status_label.configure(
                text="TERBUKA 🔓",
                fg=theme_manager.get_color('success')
            )
            self.status_indicator.set_status('unlocked')
        
        # Schedule next update
        self.master.after(500, self.update_door_status)
    
    def toggle_theme(self):
        """Toggle between light and dark theme"""
        new_theme = theme_manager.toggle_theme()
        
        # Update button text
        if theme_manager.is_dark():
            self.theme_btn.configure(text="☀️ Light Mode")
        else:
            self.theme_btn.configure(text="🌙 Dark Mode")
        
        # Reapply theme to all widgets
        self.apply_theme()
        self.show_notification(f'🎨 Theme changed to {new_theme.upper()} mode', 'info')
    
    def apply_theme(self):
        """Apply current theme to all widgets"""
        # Update master background
        self.master.configure(bg=theme_manager.get_color('bg_primary'))
        
        # Re-setup styles
        self.setup_styles()
        
        # Update all widgets recursively
        self.update_widget_colors(self.master)
    
    def update_widget_colors(self, widget):
        """Recursively update widget colors"""
        try:
            widget_type = widget.winfo_class()
            
            if widget_type == 'Frame':
                widget.configure(bg=theme_manager.get_color('bg_primary'))
            elif widget_type == 'Label':
                widget.configure(
                    bg=theme_manager.get_color('bg_card'),
                    fg=theme_manager.get_color('text_primary')
                )
            elif widget_type == 'Canvas':
                if hasattr(widget, 'draw_status'):
                    widget.draw_status()
            
            # Recurse children
            for child in widget.winfo_children():
                self.update_widget_colors(child)
        
        except:
            pass
    
    def toggle_reports_menu(self):
        """Toggle reports dropdown menu"""
        if self.reports_dropdown_visible:
            self.reports_dropdown.place_forget()
            self.reports_dropdown_visible = False
        else:
            # Position dropdown below reports button
            self.reports_dropdown.place(x=self.master.winfo_width() - 250, y=60, width=240)
            self.reports_dropdown_visible = True
            AnimationManager.slide_down(self.reports_dropdown, 300, 200)
    
    def on_window_resize(self, event):
        """Handle window resize for responsive layout"""
        if event.widget == self.master:
            self.responsive.calculate_scale()
            # Could update font sizes here based on scale factor
    
    # Report menu functions (stubs)
    def report_daily(self):
        print("📅 Daily Attendance Report - Feature coming soon")
        self.show_notification('📅 Daily Report - Coming Soon', 'info')
        self.toggle_reports_menu()
    
    def report_monthly(self):
        print("📊 Monthly Attendance Report - Feature coming soon")
        self.show_notification('📊 Monthly Report - Coming Soon', 'info')
        self.toggle_reports_menu()
    
    def report_late(self):
        print("⏰ Late/Early Report - Feature coming soon")
        self.show_notification('⏰ Late/Early Report - Coming Soon', 'info')
        self.toggle_reports_menu()
    
    def report_door(self):
        print("🚪 Doorlock Activity Log - Feature coming soon")
        self.show_notification('🚪 Doorlock Log - Coming Soon', 'info')
        self.toggle_reports_menu()
    
    def export_csv(self):
        print("💾 Export to CSV - Feature coming soon")
        self.show_notification('💾 CSV Export - Coming Soon', 'info')
        self.toggle_reports_menu()
    
    def export_pdf(self):
        print("📄 Export to PDF - Feature coming soon")
        self.show_notification('📄 PDF Export - Coming Soon', 'info')
        self.toggle_reports_menu()

# =============================================================================
# MAIN PROGRAM
# =============================================================================

def main():
    print("="*70)
    print("  DOORLOCK + ABSENSI SYSTEM v4.0 - PROFESSIONAL UI")
    print("="*70)
    
    # Initialize Database
    print("\n[1/4] Initializing Database...")
    try:
        init_db()
    except Exception as e:
        print(f"✗ FATAL: Database initialization failed: {e}")
        return
    
    # Initialize GPIO
    print("\n[2/4] Initializing GPIO...")
    gpio_ok = init_gpio()
    if not gpio_ok:
        print("⚠ Warning: GPIO not available, using simulation mode")
    
    # Start Flask API Server
    print("\n[3/4] Starting Flask API Server...")
    flask_thread = threading.Thread(target=run_flask, daemon=True)
    flask_thread.start()
    time.sleep(1)
    
    # Start Modern GUI
    print("\n[4/4] Starting Modern GUI...")
    try:
        root = tk.Tk()
        gui = ModernAbsensiGUI(root)
        
        print("\n" + "="*70)
        print("  ✓ SISTEM SIAP!")
        print("="*70)
        print(f"  Flask API  : http://0.0.0.0:{FLASK_PORT}")
        print(f"  GPIO Mode  : {'HARDWARE' if GPIO_AVAILABLE else 'SIMULATED'}")
        print(f"  Database   : {DB_CONFIG['database']}@{DB_CONFIG['host']}")
        print(f"  UI Theme   : {theme_manager.current_theme.upper()} MODE")
        print("="*70 + "\n")
        
        root.mainloop()
        
    except KeyboardInterrupt:
        print("\n\n⚠ Program dihentikan oleh user (Ctrl+C)")
    except Exception as e:
        print(f"\n✗ GUI Error: {e}")
    finally:
        print("\n🧹 Cleaning up...")
        cleanup_gpio()
        if db_conn and db_conn.is_connected():
            db_cursor.close()
            db_conn.close()
            print("✓ Database connection closed")
        print("✓ Program terminated\n")

if __name__ == "__main__":
    main()
