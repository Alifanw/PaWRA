import { useState, useEffect } from "react";
import { usePage } from "@inertiajs/react";
import Sidebar from "@/Components/Admin/Sidebar";
import Topbar from "@/Components/Admin/Topbar";
import { Toaster } from "react-hot-toast";

export default function AdminLayout({ auth, children }) {
    const { component } = usePage();
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);
    const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

    // Refresh CSRF token whenever component/page changes (user navigation)
    useEffect(() => {
        if (typeof window !== 'undefined' && window.updateCsrfToken) {
            window.updateCsrfToken();
        }
    }, [component]);

    useEffect(() => {
        const handler = () => {
            // Close mobile overlay when requested from children (links/navigation)
            if (typeof window !== 'undefined' && window.innerWidth < 1024) {
                setMobileSidebarOpen(false);
            }
        };

        window.addEventListener('sidebar:close', handler);
        return () => window.removeEventListener('sidebar:close', handler);
    }, []);

    const handleToggleSidebar = () => {
        if (typeof window !== 'undefined' && window.innerWidth < 1024) {
            setMobileSidebarOpen((s) => !s);
        } else {
            setSidebarCollapsed((s) => !s);
        }
    };

    return (
        <>
            <Toaster position="top-right" />

            <div className="flex h-screen overflow-hidden bg-white dark:bg-slate-950">
                {/* SIDEBAR */}
                <Sidebar 
                    auth={auth}
                    collapsed={sidebarCollapsed} 
                    mobileOpen={mobileSidebarOpen} 
                    onCloseMobile={() => setMobileSidebarOpen(false)} 
                />

                <div className="flex flex-1 flex-col overflow-hidden">
                    {/* TOPBAR */}
                    <header className="sticky top-0 z-50 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-700 shadow-sm">
                        <Topbar
                            auth={auth}
                            onToggleSidebar={handleToggleSidebar}
                        />
                    </header>

                    {/* CONTENT */}
                    <main className="flex-1 overflow-y-auto p-6 bg-white dark:bg-slate-950">
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
}