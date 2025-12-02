import React, { useState } from "react";
import { useTheme } from "../ThemeProvider";
import { Link, usePage } from "@inertiajs/react";

export default function AppLayout({ children, showHeader = true }) {
  const { theme, toggleTheme } = useTheme();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { auth } = usePage().props;

  return (
    <div>
      {/* Navbar (optional) */}
      {showHeader && (
        <nav className="header flex items-center h-14 px-6 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-20">
        <button
          className="lg:hidden mr-4"
          aria-label="Toggle sidebar"
          onClick={() => setSidebarOpen((v) => !v)}
        >
          <span style={{ fontSize: 24 }}>{sidebarOpen ? "✖" : "☰"}</span>
        </button>
        <Link href="/" className="font-bold text-lg flex items-center gap-2">
          <img src="/logo.svg" alt="Logo" style={{ height: 32 }} />
          <span>Dashboard</span>
        </Link>
        <div className="flex-1" />
        <button className="ml-2" onClick={toggleTheme} aria-label="Toggle theme">
          {theme === "dark" ? "🌙" : "☀️"}
        </button>
        {auth?.user && (
          <span className="ml-4 font-medium">{auth.user.name}</span>
        )}
        </nav>
      )}

      {/* Sidebar */}
      <aside
        className={`sidebar fixed top-0 left-0 h-full w-56 p-6 border-r border-gray-200 dark:border-gray-700 bg-sidebar z-10 lg:flex flex-col gap-4 ${sidebarOpen ? "flex" : "hidden"}`}
        style={{ minHeight: "100vh" }}
      >
        <Link href="/dashboard" className="mb-2">Dashboard</Link>
        <Link href="/profile" className="mb-2">Profile</Link>
        <Link href="/settings" className="mb-2">Settings</Link>
        {/* Add more links as needed */}
      </aside>

      {/* Main Content */}
      <main className="main-content" style={{ marginLeft: "224px", padding: "2rem 1.25rem", minHeight: "100vh" }}>
        {children}
      </main>
      <style>{`
        @media (max-width: 1024px) {
          .sidebar { display: none !important; }
          .sidebar.flex { display: flex !important; position: fixed; background: var(--sidebar); }
          .main-content { margin-left: 0 !important; padding: 1rem 0.5rem !important; }
        }
      `}</style>
    </div>
  );
}