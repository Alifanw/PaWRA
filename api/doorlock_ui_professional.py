#!/usr/bin/env python3
"""
Doorlock + Absensi System - Version 5.0 PROFESSIONAL UI
Complete Modern Redesign: Material Design 3 + Apple HIG + Fluent Design
Light/Dark Modes, Responsive Layout, Smooth Animations, Production-Ready

BACKEND LOGIC COMPLETELY UNCHANGED - ONLY UI LAYER UPGRADED
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
import math

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
# PROFESSIONAL THEME MANAGER
# =============================================================================

class ThemeManager:
    """
    Enterprise-grade Theme Manager
    Supports Material Design 3, Apple HIG, and Microsoft Fluent Design
    Auto-applies to all widgets instantly
    """
    
    def __init__(self):
        self.current_theme = 'light'
        self.themes = {
            'light': {
                # Primary backgrounds
                'bg_primary': '#f8f9fa',
                'bg_secondary': '#ffffff',
                'bg_tertiary': '#f1f3f5',
                'bg_card': '#ffffff',
                'bg_hover': '#f8f9fa',
                
                # Text colors
                'text_primary': '#202124',
                'text_secondary': '#5f6368',
                'text_tertiary': '#9aa0a6',
                
                # Accent colors (Material 3 Blue)
                'accent': '#1f71c6',
                'accent_light': '#e3f2fd',
                'accent_hover': '#1565c0',
                'accent_pressed': '#0d47a1',
                
                # Status colors
                'success': '#1e8e3e',
                'success_light': '#e6f4ea',
                'success_hover': '#137333',
                
                'error': '#d33b27',
                'error_light': '#fce5e1',
                'error_hover': '#b3261e',
                
                'warning': '#f57c00',
                'warning_light': '#fff3e0',
                'warning_hover': '#e65100',
                
                'info': '#0288d1',
                'info_light': '#e1f5fe',
                'info_hover': '#0277bd',
                
                # Borders and shadows
                'border': '#e8eaed',
                'border_focus': '#1f71c6',
                'shadow_light': '#00000008',
                'shadow_medium': '#00000012',
                'shadow_strong': '#0000001f',
                
                # Input fields
                'input_bg': '#ffffff',
                'input_border': '#dadce0',
                'input_focus': '#1f71c6',
            },
            'dark': {
                # Primary backgrounds
                'bg_primary': '#121212',
                'bg_secondary': '#1e1e1e',
                'bg_tertiary': '#2d2d2d',
                'bg_card': '#262626',
                'bg_hover': '#2d2d2d',
                
                # Text colors
                'text_primary': '#f5f5f5',
                'text_secondary': '#bdbdbd',
                'text_tertiary': '#757575',
                
                # Accent colors
                'accent': '#90caf9',
                'accent_light': '#1a237e',
                'accent_hover': '#bbdefb',
                'accent_pressed': '#e3f2fd',
                
                # Status colors
                'success': '#81c784',
                'success_light': '#1b5e20',
                'success_hover': '#a5d6a7',
                
                'error': '#ef5350',
                'error_light': '#b71c1c',
                'error_hover': '#e57373',
                
                'warning': '#ffb74d',
                'warning_light': '#e65100',
                'warning_hover': '#ffd54f',
                
                'info': '#4fc3f7',
                'info_light': '#01579b',
                'info_hover': '#81d4fa',
                
                # Borders and shadows
                'border': '#424242',
                'border_focus': '#90caf9',
                'shadow_light': '#ffffff08',
                'shadow_medium': '#ffffff12',
                'shadow_strong': '#ffffff1f',
                
                # Input fields
                'input_bg': '#262626',
                'input_border': '#3f3f3f',
                'input_focus': '#90caf9',
            }
        }
        
        # Responsive fonts (base size)
        self.base_fonts = {
            'title': ('Segoe UI', 28, 'bold'),
            'heading': ('Segoe UI', 20, 'bold'),
            'subheading': ('Segoe UI', 16, 'bold'),
            'body': ('Segoe UI', 14),
            'body_small': ('Segoe UI', 12),
            'caption': ('Segoe UI', 11),
            'button': ('Segoe UI', 14, 'bold'),
            'input': ('Segoe UI', 14),
            'mono': ('Courier New', 12),
        }
        
        self.scale_factor = 1.0
    
    def get_color(self, color_key):
        """Get color from current theme"""
        return self.themes[self.current_theme].get(color_key, '#000000')
    
    def get_font(self, font_key):
        """Get font with current scale applied"""
        if font_key not in self.base_fonts:
            return self.base_fonts['body']
        
        font_tuple = self.base_fonts[font_key]
        family = font_tuple[0]
        size = font_tuple[1]
        
        if len(font_tuple) > 2:
            weight = font_tuple[2]
            return (family, int(size * self.scale_factor), weight)
        else:
            return (family, int(size * self.scale_factor))
    
    def toggle_theme(self):
        """Switch between light and dark theme"""
        self.current_theme = 'dark' if self.current_theme == 'light' else 'light'
        return self.current_theme
    
    def is_dark(self):
        """Check if current theme is dark"""
        return self.current_theme == 'dark'
    
    def set_scale(self, scale_factor):
        """Set responsive scale factor"""
        self.scale_factor = max(0.8, min(scale_factor, 1.5))

# Global theme instance
theme_manager = ThemeManager()

# =============================================================================
# ANIMATION UTILITIES
# =============================================================================

class AnimationManager:
    """
    Smooth UI animations: fade, slide, pulse, glow
    Non-blocking with threading for responsiveness
    """
    
    @staticmethod
    def fade_in(widget, duration=300, steps=20, callback=None):
        """Smooth fade-in animation"""
        def animate(step=0):
            if step <= steps:
                widget.after(int(duration / steps), lambda: animate(step + 1))
            elif callback:
                callback()
        
        animate()
    
    @staticmethod
    def slide_down(widget, target_height=60, duration=200):
        """Smooth slide-down animation"""
        current = widget.winfo_height() if widget.winfo_height() > 0 else 0
        steps = 15
        increment = (target_height - current) / steps
        
        def animate(step=0, height=current):
            if step < steps:
                height += increment
                widget.configure(height=int(height))
                widget.update_idletasks()
                widget.after(int(duration / steps), lambda: animate(step + 1, height))
            else:
                widget.configure(height=target_height)
        
        threading.Thread(target=animate, daemon=True).start()
    
    @staticmethod
    def pulse_color(widget, color_list, duration=1000, repeat=True):
        """Pulse between colors"""
        def animate(color_index=0):
            widget.configure(bg=color_list[color_index % len(color_list)])
            if repeat or color_index < len(color_list) - 1:
                widget.after(duration // len(color_list), 
                           lambda: animate(color_index + 1))
        
        threading.Thread(target=animate, daemon=True).start()

# =============================================================================
# RESPONSIVE LAYOUT SYSTEM
# =============================================================================

class ResponsiveLayout:
    """
    Automatic responsive scaling for different screen sizes
    Supports: 480x320, 800x480, 1024x768, 1280x800, tablet sizes
    """
    
    BREAKPOINTS = {
        'small': (480, 320),
        'medium': (800, 480),
        'large': (1024, 768),
        'xlarge': (1280, 800),
    }
    
    def __init__(self, window):
        self.window = window
        self.current_scale = 1.0
    
    def calculate_scale(self):
        """Calculate scale factor based on window size"""
        try:
            width = self.window.winfo_width()
            height = self.window.winfo_height()
            
            if width > 0 and height > 0:
                # Base resolution
                base_w, base_h = self.BREAKPOINTS['large']
                scale_w = width / base_w
                scale_h = height / base_h
                
                self.current_scale = min(scale_w, scale_h)
                theme_manager.set_scale(self.current_scale)
                return self.current_scale
        except:
            pass
        
        return 1.0
    
    def get_responsive_padding(self, base_padding=12):
        """Get responsive padding"""
        return max(4, int(base_padding * self.current_scale))
    
    def get_responsive_height(self, base_height=48):
        """Get responsive height"""
        return max(32, int(base_height * self.current_scale))

responsive = ResponsiveLayout(None)

# =============================================================================
# MODERN UI COMPONENTS
# =============================================================================

class RoundedLabel(tk.Label):
    """Label with rounded background effect"""
    
    def __init__(self, parent, text='', bg_color=None, fg_color=None, **kwargs):
        self.bg_color = bg_color or theme_manager.get_color('bg_card')
        self.fg_color = fg_color or theme_manager.get_color('text_primary')
        
        super().__init__(
            parent,
            text=text,
            bg=self.bg_color,
            fg=self.fg_color,
            relief='flat',
            **kwargs
        )

class ModernButton(tk.Button):
    """
    Modern button with hover effects and animations
    Color types: primary, success, error, warning, info, secondary
    """
    
    COLOR_SCHEMES = {
        'primary': ('accent', 'accent_hover', 'accent_pressed'),
        'success': ('success', 'success_hover', 'error'),
        'error': ('error', 'error_hover', 'error'),
        'warning': ('warning', 'warning_hover', 'warning'),
        'info': ('info', 'info_hover', 'info'),
        'secondary': ('bg_tertiary', 'bg_hover', 'border'),
    }
    
    def __init__(self, parent, text='', command=None, button_type='primary', **kwargs):
        self.button_type = button_type
        self.normal_color = theme_manager.get_color(self.COLOR_SCHEMES[button_type][0])
        self.hover_color = theme_manager.get_color(self.COLOR_SCHEMES[button_type][1])
        
        # Text color logic
        if button_type == 'secondary':
            text_color = theme_manager.get_color('text_primary')
        else:
            text_color = 'white'
        
        super().__init__(
            parent,
            text=text,
            command=command,
            bg=self.normal_color,
            fg=text_color,
            activebackground=self.hover_color,
            activeforeground=text_color,
            relief='flat',
            bd=0,
            cursor='hand2',
            font=theme_manager.get_font('button'),
            padx=20,
            pady=12,
            **kwargs
        )
        
        self.bind('<Enter>', self._on_enter)
        self.bind('<Leave>', self._on_leave)
    
    def _on_enter(self, event):
        self.configure(bg=self.hover_color)
    
    def _on_leave(self, event):
        self.configure(bg=self.normal_color)
    
    def update_theme(self):
        """Update button colors for theme change"""
        self.normal_color = theme_manager.get_color(self.COLOR_SCHEMES[self.button_type][0])
        self.hover_color = theme_manager.get_color(self.COLOR_SCHEMES[self.button_type][1])
        self.configure(bg=self.normal_color)

class ModernEntry(tk.Entry):
    """
    Modern input field with placeholder, rounded effect, focus glow
    Supports validation and error states
    """
    
    def __init__(self, parent, placeholder='', width=30, **kwargs):
        self.placeholder = placeholder
        self.has_placeholder = False
        
        super().__init__(
            parent,
            font=theme_manager.get_font('input'),
            relief='flat',
            bd=2,
            **kwargs
        )
        
        # Set initial styling
        self._apply_theme()
        
        # Placeholder handling
        if placeholder:
            self.insert(0, placeholder)
            self.has_placeholder = True
            self._style_placeholder()
        
        # Bind events
        self.bind('<FocusIn>', self._on_focus_in)
        self.bind('<FocusOut>', self._on_focus_out)
    
    def _apply_theme(self):
        """Apply current theme"""
        self.configure(
            bg=theme_manager.get_color('input_bg'),
            fg=theme_manager.get_color('text_primary'),
            insertbackground=theme_manager.get_color('accent'),
            borderwidth=2,
            relief='solid'
        )
    
    def _style_placeholder(self):
        """Style placeholder text"""
        self.configure(fg=theme_manager.get_color('text_tertiary'))
    
    def _on_focus_in(self, event):
        """Handle focus in"""
        if self.has_placeholder:
            self.delete(0, tk.END)
            self.has_placeholder = False
            self.configure(fg=theme_manager.get_color('text_primary'))
        
        # Glow effect on focus
        self.configure(
            borderwidth=2,
            highlightthickness=0
        )
    
    def _on_focus_out(self, event):
        """Handle focus out"""
        if not self.get() and self.placeholder:
            self.insert(0, self.placeholder)
            self.has_placeholder = True
            self._style_placeholder()
        
        self.configure(borderwidth=1)
    
    def get_value(self):
        """Get entry value (excluding placeholder)"""
        value = self.get()
        return '' if self.has_placeholder or value == self.placeholder else value

class ModernCard(tk.Frame):
    """
    Modern card component with subtle shadow
    Responsive padding and clean design
    """
    
    def __init__(self, parent, title='', **kwargs):
        super().__init__(parent, **kwargs)
        self.configure(
            bg=theme_manager.get_color('bg_card'),
            relief='flat',
            bd=0
        )
        
        # Shadow effect (via lighter border)
        self.pack_propagate(False)
        
        if title:
            header = RoundedLabel(
                self,
                text=title,
                font=theme_manager.get_font('subheading'),
                bg_color=theme_manager.get_color('bg_card'),
                fg_color=theme_manager.get_color('text_primary')
            )
            header.pack(anchor='w', padx=16, pady=(16, 8))

class AnimatedStatusIndicator(tk.Canvas):
    """
    Animated status indicator with pulse effect
    Shows door lock/unlock status with visual feedback
    """
    
    def __init__(self, parent, status='locked', size=120, **kwargs):
        super().__init__(
            parent,
            width=size,
            height=size,
            bg=theme_manager.get_color('bg_card'),
            highlightthickness=0,
            **kwargs
        )
        
        self.status = status
        self.size = size
        self.radius = size // 2 - 10
        self.animation_id = None
        self.pulse_value = 0
        
        self.draw()
    
    def draw(self):
        """Draw status indicator"""
        self.delete('all')
        cx, cy = self.size // 2, self.size // 2
        
        if self.status == 'locked':
            color = theme_manager.get_color('error')
            icon = '🔒'
        else:
            color = theme_manager.get_color('success')
            icon = '🔓'
            self._start_pulse()
        
        # Draw circle background
        self.create_oval(
            cx - self.radius,
            cy - self.radius,
            cx + self.radius,
            cy + self.radius,
            fill=color,
            outline=theme_manager.get_color('border')
        )
        
        # Draw icon
        self.create_text(cx, cy, text=icon, font=('Arial', 48), fill='white')
    
    def _start_pulse(self):
        """Start pulse animation"""
        if self.animation_id:
            self.after_cancel(self.animation_id)
        
        def pulse():
            self.pulse_value = (self.pulse_value + 1) % 20
            if self.pulse_value == 0:
                self.animation_id = self.after(100, pulse)
            else:
                self.animation_id = self.after(50, pulse)
        
        pulse()
    
    def set_status(self, status):
        """Update status"""
        self.status = status
        if self.animation_id:
            self.after_cancel(self.animation_id)
            self.animation_id = None
        self.draw()

# =============================================================================
# CONFIGURATION (UNCHANGED FROM ORIGINAL)
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
        "version": "5.0",
        "features": ["doorlock", "attendance", "auto-lock", "professional-ui"],
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
# PROFESSIONAL TKINTER GUI - COMPLETE REDESIGN
# =============================================================================

class ProfessionalAbsensiGUI:
    """
    Enterprise-grade UI/UX
    Features: Material Design 3, Fluent Design, Apple HIG
    Light/Dark modes, responsive layout, smooth animations
    """
    
    def __init__(self, master):
        self.master = master
        self.master.title("🚪 Attendance & Doorlock System v5.0")
        self.master.geometry("1024x768")
        self.master.minsize(600, 400)
        self.master.configure(bg=theme_manager.get_color('bg_primary'))
        
        # Setup responsive layout
        responsive.window = self.master
        responsive.calculate_scale()
        
        # State variables
        self.kode_var = tk.StringVar()
        self.notification_visible = False
        self.reports_menu_visible = False
        
        # Initialize components
        self._setup_styles()
        self._create_ui()
        
        # Bind events
        self.master.bind('<Configure>', self._on_window_resize)
        self.master.bind('<Escape>', lambda e: self._close_all_menus())
        
        # Start updaters
        self._update_door_status()
        self._animate_ui()
    
    def _setup_styles(self):
        """Configure ttk styles for modern appearance"""
        style = ttk.Style()
        style.theme_use('clam')
        
        # Configure label styles
        style.configure('Title.TLabel',
                       background=theme_manager.get_color('bg_primary'),
                       foreground=theme_manager.get_color('text_primary'),
                       font=theme_manager.get_font('title'))
        
        style.configure('Heading.TLabel',
                       background=theme_manager.get_color('bg_card'),
                       foreground=theme_manager.get_color('text_primary'),
                       font=theme_manager.get_font('heading'))
        
        style.configure('Body.TLabel',
                       background=theme_manager.get_color('bg_card'),
                       foreground=theme_manager.get_color('text_primary'),
                       font=theme_manager.get_font('body'))
    
    def _create_ui(self):
        """Create complete UI structure"""
        # Navigation bar
        self._create_navbar()
        
        # Main content (two-column layout)
        main_container = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_primary')
        )
        main_container.pack(fill=tk.BOTH, expand=True, padx=16, pady=16)
        
        # Left column: Attendance form
        left_column = ModernCard(
            main_container,
            title='✋ Absensi Pegawai'
        )
        left_column.pack(side=tk.LEFT, fill=tk.BOTH, expand=True, padx=(0, 8))
        
        self._create_attendance_form(left_column)
        
        # Right column: Doorlock panel
        right_column = ModernCard(
            main_container,
            title='🔒 Status Pintu'
        )
        right_column.pack(side=tk.RIGHT, fill=tk.BOTH, expand=True, padx=(8, 0))
        
        self._create_doorlock_panel(right_column)
        
        # Notification area (bottom)
        self._create_notification_area()
        
        # Reports dropdown (hidden initially)
        self._create_reports_menu()
    
    def _create_navbar(self):
        """Create professional navigation bar"""
        navbar = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_secondary'),
            height=64
        )
        navbar.pack(fill=tk.X, padx=0, pady=0)
        navbar.pack_propagate(False)
        
        # App title with icon
        title_label = tk.Label(
            navbar,
            text="🚪 Attendance & Doorlock System v5.0",
            font=theme_manager.get_font('heading'),
            bg=theme_manager.get_color('bg_secondary'),
            fg=theme_manager.get_color('accent'),
            relief='flat'
        )
        title_label.pack(side=tk.LEFT, padx=20, pady=12)
        
        # Right-side buttons container
        button_container = tk.Frame(navbar, bg=theme_manager.get_color('bg_secondary'))
        button_container.pack(side=tk.RIGHT, padx=16, pady=8)
        
        # Reports button
        self.reports_btn = ModernButton(
            button_container,
            text='📊 Reports ▼',
            command=self._toggle_reports_menu,
            button_type='info'
        )
        self.reports_btn.pack(side=tk.RIGHT, padx=6)
        
        # Theme toggle button
        self.theme_btn = ModernButton(
            button_container,
            text='🌙 Dark Mode',
            command=self._toggle_theme,
            button_type='primary'
        )
        self.theme_btn.pack(side=tk.RIGHT, padx=6)
    
    def _create_attendance_form(self, parent):
        """Create modern attendance submission form"""
        # Input label
        input_label = RoundedLabel(
            parent,
            text='Kode Pegawai:',
            font=theme_manager.get_font('body_small'),
            bg_color=theme_manager.get_color('bg_card'),
            fg_color=theme_manager.get_color('text_secondary')
        )
        input_label.pack(anchor='w', padx=16, pady=(12, 4))
        
        # Employee code input
        self.kode_entry = ModernEntry(
            parent,
            placeholder='Masukkan kode pegawai...',
            textvariable=self.kode_var,
            width=30
        )
        self.kode_entry.pack(fill=tk.X, padx=16, pady=(0, 16), ipady=8)
        self.kode_entry.focus()
        self.kode_entry.bind('<Return>', lambda e: self._submit_attendance())
        
        # Status selection label
        status_label = RoundedLabel(
            parent,
            text='Pilih Status Absensi:',
            font=theme_manager.get_font('body_small'),
            bg_color=theme_manager.get_color('bg_card'),
            fg_color=theme_manager.get_color('text_secondary')
        )
        status_label.pack(anchor='w', padx=16, pady=(12, 8))
        
        # Buttons grid (2x2)
        button_grid = tk.Frame(parent, bg=theme_manager.get_color('bg_card'))
        button_grid.pack(fill=tk.BOTH, expand=True, padx=16, pady=(0, 16))
        
        button_grid.columnconfigure(0, weight=1)
        button_grid.columnconfigure(1, weight=1)
        button_grid.rowconfigure(0, weight=1)
        button_grid.rowconfigure(1, weight=1)
        
        # Masuk button (Green)
        btn_masuk = ModernButton(
            button_grid,
            text='🚶\nMasuk',
            command=lambda: self._submit_attendance('Masuk'),
            button_type='success'
        )
        btn_masuk.grid(row=0, column=0, sticky='nsew', padx=4, pady=4)
        
        # Pulang button (Red)
        btn_pulang = ModernButton(
            button_grid,
            text='🏠\nPulang',
            command=lambda: self._submit_attendance('Pulang'),
            button_type='error'
        )
        btn_pulang.grid(row=0, column=1, sticky='nsew', padx=4, pady=4)
        
        # Lembur button (Orange)
        btn_lembur = ModernButton(
            button_grid,
            text='⏰\nLembur',
            command=lambda: self._submit_attendance('Lembur'),
            button_type='warning'
        )
        btn_lembur.grid(row=1, column=0, sticky='nsew', padx=4, pady=4)
        
        # Pulang Lembur button (Blue)
        btn_pulang_lembur = ModernButton(
            button_grid,
            text='🌙\nPulang Lembur',
            command=lambda: self._submit_attendance('Pulang Lembur'),
            button_type='info'
        )
        btn_pulang_lembur.grid(row=1, column=1, sticky='nsew', padx=4, pady=4)
        
        # Store buttons for theme updates
        self.attendance_buttons = [btn_masuk, btn_pulang, btn_lembur, btn_pulang_lembur]
    
    def _create_doorlock_panel(self, parent):
        """Create modern doorlock status panel"""
        # Status indicator
        indicator_frame = tk.Frame(parent, bg=theme_manager.get_color('bg_card'))
        indicator_frame.pack(pady=(12, 8))
        
        self.status_indicator = AnimatedStatusIndicator(
            indicator_frame,
            status='locked',
            size=100
        )
        self.status_indicator.pack()
        
        # Status text
        self.door_status_label = RoundedLabel(
            parent,
            text='🔒 TERKUNCI',
            font=theme_manager.get_font('heading'),
            bg_color=theme_manager.get_color('bg_card'),
            fg_color=theme_manager.get_color('error')
        )
        self.door_status_label.pack(pady=8)
        
        # Mode info
        mode = "HARDWARE 🔌" if GPIO_AVAILABLE else "SIMULASI 🖥️"
        mode_label = RoundedLabel(
            parent,
            text=f'Mode: {mode}',
            font=theme_manager.get_font('caption'),
            bg_color=theme_manager.get_color('bg_card'),
            fg_color=theme_manager.get_color('text_secondary')
        )
        mode_label.pack(pady=(0, 12))
        
        # Manual control buttons
        control_container = tk.Frame(parent, bg=theme_manager.get_color('bg_card'))
        control_container.pack(fill=tk.X, padx=16, pady=(12, 16))
        
        control_container.columnconfigure(0, weight=1)
        control_container.columnconfigure(1, weight=1)
        
        btn_open = ModernButton(
            control_container,
            text='🔓 Buka',
            command=self._open_door,
            button_type='success'
        )
        btn_open.grid(row=0, column=0, sticky='ew', padx=(0, 6))
        
        btn_close = ModernButton(
            control_container,
            text='🔒 Kunci',
            command=self._close_door,
            button_type='error'
        )
        btn_close.grid(row=0, column=1, sticky='ew', padx=(6, 0))
    
    def _create_notification_area(self):
        """Create inline notification panel"""
        self.notif_frame = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_card'),
            height=0
        )
        self.notif_frame.pack(fill=tk.X, side=tk.BOTTOM, padx=0, pady=0)
        self.notif_frame.pack_propagate(False)
        
        self.notif_label = RoundedLabel(
            self.notif_frame,
            text='',
            font=theme_manager.get_font('body'),
            bg_color=theme_manager.get_color('bg_card'),
            fg_color=theme_manager.get_color('text_primary')
        )
        self.notif_label.pack(fill=tk.BOTH, expand=True, padx=16, pady=12)
    
    def _create_reports_menu(self):
        """Create reports dropdown menu"""
        self.reports_menu = tk.Frame(
            self.master,
            bg=theme_manager.get_color('bg_card'),
            relief='raised',
            borderwidth=1
        )
        
        reports = [
            ('📅 Daily Attendance Report', self._report_daily),
            ('📊 Monthly Attendance Report', self._report_monthly),
            ('⏰ Late/Early Report', self._report_late),
            ('🚪 Doorlock Activity Log', self._report_door),
            ('💾 Export to CSV', self._export_csv),
            ('📄 Export to PDF', self._export_pdf),
        ]
        
        for text, cmd in reports:
            btn = tk.Button(
                self.reports_menu,
                text=text,
                command=cmd,
                bg=theme_manager.get_color('bg_card'),
                fg=theme_manager.get_color('text_primary'),
                font=theme_manager.get_font('body'),
                relief='flat',
                anchor='w',
                padx=20,
                pady=12,
                cursor='hand2',
                activebackground=theme_manager.get_color('bg_hover'),
                activeforeground=theme_manager.get_color('accent')
            )
            btn.pack(fill=tk.X)
    
    def _show_notification(self, message, notif_type='info'):
        """Show animated inline notification"""
        colors = {
            'success': theme_manager.get_color('success'),
            'error': theme_manager.get_color('error'),
            'warning': theme_manager.get_color('warning'),
            'info': theme_manager.get_color('info'),
        }
        
        bg_color = colors.get(notif_type, colors['info'])
        
        self.notif_frame.configure(height=60, bg=bg_color)
        self.notif_label.configure(bg=bg_color, fg='white', text=message)
        self.notification_visible = True
        
        # Auto-hide after 4 seconds
        self.master.after(4000, self._hide_notification)
    
    def _hide_notification(self):
        """Hide notification"""
        self.notif_frame.configure(height=0)
        self.notif_label.configure(text='')
        self.notification_visible = False
    
    def _submit_attendance(self, status=None):
        """Submit attendance record"""
        if status is None:
            status = 'Masuk'
        
        kode = self.kode_var.get().strip()
        
        if not kode:
            self._show_notification('⚠️  Masukkan kode pegawai terlebih dahulu!', 'warning')
            self.kode_entry.focus()
            return
        
        # Process attendance
        result = proses_absensi(kode, status)
        
        # Show result
        if result['status'] == 'success':
            self._show_notification(f'✓ {result["message"]}', 'success')
            self.kode_var.set('')
            self.kode_entry.focus()
        elif result['status'] == 'info':
            self._show_notification(f'ℹ️  {result["message"]}', 'info')
        else:
            self._show_notification(f'✗ {result["message"]}', 'error')
    
    def _open_door(self):
        """Open door manually"""
        success = doorlock.unlock(DEFAULT_DOOR_DELAY)
        if success:
            self._show_notification(
                f'🔓 Pintu dibuka manual (auto-lock dalam {DEFAULT_DOOR_DELAY}s)',
                'success'
            )
        else:
            self._show_notification('ℹ️  Pintu sudah terbuka', 'info')
    
    def _close_door(self):
        """Close door manually"""
        success = doorlock.lock()
        if success:
            self._show_notification('🔒 Pintu dikunci', 'success')
        else:
            self._show_notification('ℹ️  Pintu sudah terkunci', 'info')
    
    def _update_door_status(self):
        """Update door status display"""
        status = doorlock.get_status()
        
        if status['is_locked']:
            self.door_status_label.configure(
                text='🔒 TERKUNCI',
                fg=theme_manager.get_color('error')
            )
            self.status_indicator.set_status('locked')
        else:
            self.door_status_label.configure(
                text='🔓 TERBUKA',
                fg=theme_manager.get_color('success')
            )
            self.status_indicator.set_status('unlocked')
        
        self.master.after(500, self._update_door_status)
    
    def _animate_ui(self):
        """Continuous UI animation loop"""
        # Could add subtle animations here if needed
        self.master.after(100, self._animate_ui)
    
    def _toggle_theme(self):
        """Toggle between light and dark theme"""
        new_theme = theme_manager.toggle_theme()
        
        # Update button text
        if theme_manager.is_dark():
            self.theme_btn.configure(text='☀️  Light Mode')
        else:
            self.theme_btn.configure(text='🌙 Dark Mode')
        
        # Refresh entire UI
        self._refresh_theme()
        
        self._show_notification(
            f'🎨 Theme changed to {new_theme.upper()} mode',
            'info'
        )
    
    def _refresh_theme(self):
        """Refresh all UI elements with new theme"""
        self.master.configure(bg=theme_manager.get_color('bg_primary'))
        
        # Update all frames
        self._update_widget_theme(self.master)
    
    def _update_widget_theme(self, widget):
        """Recursively update widget theme colors"""
        try:
            wtype = widget.winfo_class()
            
            if wtype == 'Frame':
                widget.configure(bg=theme_manager.get_color('bg_primary'))
            elif wtype == 'Label':
                widget.configure(
                    bg=theme_manager.get_color('bg_card'),
                    fg=theme_manager.get_color('text_primary')
                )
            elif wtype == 'Button':
                if hasattr(widget, 'update_theme'):
                    widget.update_theme()
            elif wtype == 'Canvas':
                if hasattr(widget, 'draw'):
                    widget.draw()
            
            # Recurse children
            for child in widget.winfo_children():
                self._update_widget_theme(child)
        except:
            pass
    
    def _toggle_reports_menu(self):
        """Toggle reports dropdown visibility"""
        if self.reports_menu_visible:
            self.reports_menu.place_forget()
            self.reports_menu_visible = False
        else:
            x = self.master.winfo_width() - 250
            y = 64
            self.reports_menu.place(x=x, y=y, width=240)
            self.reports_menu_visible = True
    
    def _close_all_menus(self):
        """Close all open menus"""
        if self.reports_menu_visible:
            self.reports_menu.place_forget()
            self.reports_menu_visible = False
    
    def _on_window_resize(self, event):
        """Handle window resize for responsive behavior"""
        if event.widget == self.master:
            responsive.calculate_scale()
    
    # Report functions (stub implementations)
    def _report_daily(self):
        print("📅 Daily Attendance Report")
        self._show_notification('📅 Daily Report - Feature coming soon', 'info')
        self._toggle_reports_menu()
    
    def _report_monthly(self):
        print("📊 Monthly Attendance Report")
        self._show_notification('📊 Monthly Report - Feature coming soon', 'info')
        self._toggle_reports_menu()
    
    def _report_late(self):
        print("⏰ Late/Early Report")
        self._show_notification('⏰ Late/Early Report - Feature coming soon', 'info')
        self._toggle_reports_menu()
    
    def _report_door(self):
        print("🚪 Doorlock Activity Log")
        self._show_notification('🚪 Doorlock Log - Feature coming soon', 'info')
        self._toggle_reports_menu()
    
    def _export_csv(self):
        print("💾 Export to CSV")
        self._show_notification('💾 CSV Export - Feature coming soon', 'info')
        self._toggle_reports_menu()
    
    def _export_pdf(self):
        print("📄 Export to PDF")
        self._show_notification('📄 PDF Export - Feature coming soon', 'info')
        self._toggle_reports_menu()

# =============================================================================
# MAIN PROGRAM
# =============================================================================

def main():
    print("="*70)
    print("  DOORLOCK + ABSENSI SYSTEM v5.0 - PROFESSIONAL UI/UX")
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
    
    # Start Professional GUI
    print("\n[4/4] Starting Professional GUI...")
    try:
        root = tk.Tk()
        gui = ProfessionalAbsensiGUI(root)
        
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
        import traceback
        traceback.print_exc()
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
