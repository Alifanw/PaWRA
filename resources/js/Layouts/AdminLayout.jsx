import { useState } from 'react';
import { Head } from '@inertiajs/react';
import Sidebar from '@/Components/Admin/Sidebar';
import Topbar from '@/Components/Admin/Topbar';
import { Toaster } from 'react-hot-toast';

export default function AdminLayout({ auth, title, children }) {
    const [sidebarCollapsed, setSidebarCollapsed] = useState(false);

    return (
        <>
            <Head title={title} />
            <Toaster position="top-right" />

            <div className="flex h-screen overflow-hidden bg-gray-100">
                {/* Sidebar */}
                <Sidebar collapsed={sidebarCollapsed} />

                {/* Main content */}
                <div className="flex flex-1 flex-col overflow-hidden">
                    {/* Topbar */}
                    <Topbar
                        auth={auth}
                        onToggleSidebar={() => setSidebarCollapsed(!sidebarCollapsed)}
                    />

                    {/* Page content */}
                    <main className="flex-1 overflow-y-auto p-6">
                        {children}
                    </main>
                </div>
            </div>
        </>
    );
}
