#!/usr/bin/env python3
"""
QUICK START GUIDE - Doorlock + Absensi System v5.0

This file demonstrates how to use the new professional UI system.
"""

# ============================================================================
# BASIC USAGE
# ============================================================================

# Start the application
if __name__ == "__main__":
    import sys
    sys.path.insert(0, '/var/www/airpanas/api')
    
    from doorlock_ui_professional import main
    main()

# ============================================================================
# ACCESSING THEME MANAGER
# ============================================================================

from doorlock_ui_professional import theme_manager

# Get current theme
current = theme_manager.current_theme  # 'light' or 'dark'

# Get a color
bg_color = theme_manager.get_color('bg_card')
text_color = theme_manager.get_color('text_primary')
accent = theme_manager.get_color('accent')

# Get a font
font = theme_manager.get_font('heading')  # ('Segoe UI', 20, 'bold')
button_font = theme_manager.get_font('button')

# Toggle theme
new_theme = theme_manager.toggle_theme()  # Switches light ↔ dark

# Check if dark mode
if theme_manager.is_dark():
    print("Dark mode is active")

# ============================================================================
# CREATING MODERN COMPONENTS
# ============================================================================

import tkinter as tk
from doorlock_ui_professional import (
    ModernButton,
    ModernEntry,
    ModernCard,
    RoundedLabel,
    AnimatedStatusIndicator,
    theme_manager
)

root = tk.Tk()
root.geometry("600x400")
root.configure(bg=theme_manager.get_color('bg_primary'))

# Create a card
card = ModernCard(root, title="Example Card")
card.pack(fill=tk.BOTH, expand=True, padx=16, pady=16)

# Create input field
entry = ModernEntry(
    card,
    placeholder="Enter value...",
    width=30
)
entry.pack(fill=tk.X, padx=16, pady=8)

# Create buttons
btn1 = ModernButton(
    card,
    text="Primary Button",
    command=lambda: print("Clicked!"),
    button_type='primary'
)
btn1.pack(padx=16, pady=4, fill=tk.X)

btn2 = ModernButton(
    card,
    text="Success Button",
    command=lambda: print("Success!"),
    button_type='success'
)
btn2.pack(padx=16, pady=4, fill=tk.X)

btn3 = ModernButton(
    card,
    text="Error Button",
    command=lambda: print("Error!"),
    button_type='error'
)
btn3.pack(padx=16, pady=4, fill=tk.X)

# Create label
label = RoundedLabel(
    card,
    text="This is a label",
    font=theme_manager.get_font('body')
)
label.pack(padx=16, pady=8)

# Create status indicator
indicator = AnimatedStatusIndicator(card, status='unlocked', size=100)
indicator.pack(pady=16)

root.mainloop()

# ============================================================================
# ANIMATIONS
# ============================================================================

from doorlock_ui_professional import AnimationManager

# Fade in a widget
AnimationManager.fade_in(widget, duration=300, steps=20)

# Slide down (like dropdown)
AnimationManager.slide_down(widget, target_height=60, duration=200)

# Pulse between colors
colors = ['#ffffff', '#f0f0f0']
AnimationManager.pulse_color(widget, colors, duration=1000, repeat=True)

# ============================================================================
# RESPONSIVE LAYOUT
# ============================================================================

from doorlock_ui_professional import responsive

# Calculate scale factor based on window size
scale = responsive.calculate_scale()

# Get responsive padding (scales with window)
padding = responsive.get_responsive_padding(base_padding=16)

# Get responsive height (for buttons, etc.)
btn_height = responsive.get_responsive_height(base_height=48)

# ============================================================================
# NOTIFICATIONS
# ============================================================================

# In the GUI class:
def show_examples():
    # Success notification
    self._show_notification('✓ Action completed successfully!', 'success')
    
    # Error notification
    self._show_notification('✗ An error occurred!', 'error')
    
    # Warning notification
    self._show_notification('⚠️  Warning: Check this!', 'warning')
    
    # Info notification
    self._show_notification('ℹ️  Information message', 'info')

# Notifications auto-hide after 4 seconds

# ============================================================================
# CREATING CUSTOM COMPONENTS
# ============================================================================

import tkinter as tk
from doorlock_ui_professional import theme_manager

class CustomWidget(tk.Frame):
    def __init__(self, parent):
        super().__init__(parent)
        
        # Get colors from theme manager
        self.configure(
            bg=theme_manager.get_color('bg_card'),
            relief='flat',
            bd=0
        )
        
        # Create label with theme-aware colors
        label = tk.Label(
            self,
            text="Custom Widget",
            bg=theme_manager.get_color('bg_card'),
            fg=theme_manager.get_color('text_primary'),
            font=theme_manager.get_font('heading')
        )
        label.pack(padx=16, pady=16)

# ============================================================================
# THEME SWITCHING
# ============================================================================

def toggle_theme(gui_instance):
    """Toggle between light and dark theme"""
    new_theme = theme_manager.toggle_theme()
    
    # Update button text
    if theme_manager.is_dark():
        gui_instance.theme_btn.configure(text='☀️  Light Mode')
    else:
        gui_instance.theme_btn.configure(text='🌙 Dark Mode')
    
    # Refresh UI
    gui_instance._refresh_theme()
    
    # Show notification
    gui_instance._show_notification(
        f'🎨 Theme changed to {new_theme.upper()} mode',
        'info'
    )

# ============================================================================
# COLOR REFERENCE
# ============================================================================

"""
Light Mode Colors:
  - bg_primary: #f8f9fa (main background)
  - bg_secondary: #ffffff (navbar)
  - bg_card: #ffffff (cards)
  - text_primary: #202124 (main text)
  - accent: #1f71c6 (blue)
  - success: #1e8e3e (green)
  - error: #d33b27 (red)
  - warning: #f57c00 (orange)
  - info: #0288d1 (light blue)

Dark Mode Colors:
  - bg_primary: #121212 (main background)
  - bg_secondary: #1e1e1e (navbar)
  - bg_card: #262626 (cards)
  - text_primary: #f5f5f5 (main text)
  - accent: #90caf9 (light blue)
  - success: #81c784 (light green)
  - error: #ef5350 (light red)
  - warning: #ffb74d (light orange)
  - info: #4fc3f7 (light blue)
"""

# ============================================================================
# BUTTON TYPES
# ============================================================================

"""
Available button types:
  - 'primary': Main action button (accent color)
  - 'success': Positive action (green)
  - 'error': Destructive action (red)
  - 'warning': Caution action (orange)
  - 'info': Informational action (blue)
  - 'secondary': Neutral action (subtle)
"""

# ============================================================================
# FONT TYPES
# ============================================================================

"""
Available font keys:
  - 'title': Large heading (28px)
  - 'heading': Section header (20px)
  - 'subheading': Card title (16px)
  - 'body': Main text (14px)
  - 'body_small': Small text (12px)
  - 'caption': Metadata (11px)
  - 'button': Button text (14px, bold)
  - 'input': Form fields (14px)
  - 'mono': Monospace (12px)

All fonts automatically scale based on responsive layout.
"""

# ============================================================================
# DATABASE & BACKEND (UNCHANGED)
# ============================================================================

"""
The following are completely unchanged:
  - Database connections and queries
  - GPIO control and relay management
  - Attendance recording logic
  - Doorlock timing and auto-lock
  - Flask API routes
  - Authentication tokens
  - All business logic

Only the UI/UX layer has been upgraded.
"""

# ============================================================================
# KEYBOARD SHORTCUTS
# ============================================================================

"""
Default shortcuts:
  - Enter: Submit attendance when focused on employee code input
  - Escape: Close all menus (reports dropdown)
  
Add more shortcuts by binding to root window:
  root.bind('<Ctrl-s>', save_callback)
  root.bind('<Ctrl-q>', quit_callback)
"""

# ============================================================================
# TESTING
# ============================================================================

def test_theme_switching():
    """Test theme switching"""
    root = tk.Tk()
    root.geometry("400x300")
    
    label = tk.Label(root, text="Theme Test")
    label.pack(pady=20)
    
    def switch():
        new = theme_manager.toggle_theme()
        label.configure(
            bg=theme_manager.get_color('bg_card'),
            fg=theme_manager.get_color('text_primary')
        )
        root.configure(bg=theme_manager.get_color('bg_primary'))
        root.title(f"Current: {new}")
    
    btn = ModernButton(root, text="Toggle Theme", command=switch)
    btn.pack(pady=10)
    
    root.configure(bg=theme_manager.get_color('bg_primary'))
    root.mainloop()

if __name__ == "__main__":
    # Uncomment to test theme switching
    # test_theme_switching()
    pass

# ============================================================================
# TROUBLESHOOTING
# ============================================================================

"""
Issue: Theme not updating
Solution: Call gui._refresh_theme() manually

Issue: Notification not appearing
Solution: Check if previous notification is still visible (4s timeout)

Issue: Components not sizing correctly
Solution: Call responsive.calculate_scale() after window resize

Issue: Font looks wrong
Solution: Check theme_manager.scale_factor is correct (0.8-1.5 range)

Issue: Colors not matching expected
Solution: Verify theme_manager.current_theme is correct

Issue: Animations stuttering
Solution: Check system resources, reduce animation steps if needed
"""

# ============================================================================
# BEST PRACTICES
# ============================================================================

"""
1. Always use theme_manager for colors, never hardcode colors
2. Always use theme_manager.get_font() for consistent typography
3. Use ModernButton for all buttons (consistent styling)
4. Use ModernEntry for all input fields
5. Use ModernCard for content containers
6. Use _show_notification() instead of messagebox
7. Use relative positioning with pack/grid for responsiveness
8. Test on different window sizes during development
9. Always check theme before creating widgets
10. Use AnimationManager for smooth transitions
"""

print("✓ Quick Start Guide loaded")
print("Run this file to see usage examples")
