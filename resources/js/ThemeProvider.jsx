'use client'; // Jika Anda pakai Next.js App Router

import { createContext, useContext, useEffect, useState } from 'react';

const ThemeContext = createContext();

export function ThemeProvider({ children }) {
  // Ambil tema awal: dari localStorage, lalu fallback ke preferensi OS
  const getInitialTheme = () => {
    if (typeof window === 'undefined') return 'light';
    const stored = localStorage.getItem('absensiDarkMode');
    if (stored !== null) {
      return stored === '1' ? 'dark' : 'light';
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  };

  const [theme, setTheme] = useState(getInitialTheme);

  useEffect(() => {
    const html = document.documentElement;

    // 1. Atur atribut data-theme → untuk CSS custom Anda (opsional, tapi aman dipertahankan)
    html.setAttribute('data-theme', theme);

    // 2. Atur kelas "dark" di <html> → WAJIB untuk Tailwind dark mode
    if (theme === 'dark') {
      html.classList.add('dark');
    } else {
      html.classList.remove('dark');
    }

    // 3. Simpan ke localStorage
    localStorage.setItem('absensiDarkMode', theme === 'dark' ? '1' : '0');

    // 4. (Opsional) Kirim event jika ada komponen lain yang perlu tahu
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: theme }));
  }, [theme]);

  const toggleTheme = () => {
    setTheme((prev) => (prev === 'dark' ? 'light' : 'dark'));
  };

  return (
    <ThemeContext.Provider value={{ theme, toggleTheme, setTheme }}>
      {children}
    </ThemeContext.Provider>
  );
}

// Hook untuk akses tema di komponen lain
export function useTheme() {
  const context = useContext(ThemeContext);
  if (!context) {
    throw new Error('useTheme must be used within a ThemeProvider');
  }
  return context;
}