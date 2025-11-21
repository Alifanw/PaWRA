import { Link, usePage } from '@inertiajs/react';
import {
    HomeIcon,
    ShoppingBagIcon,
    CalendarDaysIcon,
    TicketIcon,
    UsersIcon,
    ChartBarIcon,
    Cog6ToothIcon,
    BuildingStorefrontIcon,
    ClipboardDocumentListIcon,
} from '@heroicons/react/24/outline';

const navigation = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: HomeIcon, permission: '*' },
    { name: 'Products', href: '/admin/products', icon: BuildingStorefrontIcon, permission: '*' },
    { name: 'Bookings', href: '/admin/bookings', icon: CalendarDaysIcon, permission: '*' },
    { name: 'Ticket Sales', href: '/admin/ticket-sales', icon: TicketIcon, permission: '*' },
    {
        name: 'Reports',
        icon: ChartBarIcon,
        children: [
            { name: 'Booking Reports', href: '/admin/reports/bookings', permission: '*' },
            { name: 'Sales Reports', href: '/admin/reports/ticket-sales', permission: '*' },
        ],
    },
    { name: 'Users', href: '/admin/users', icon: UsersIcon, permission: '*' },
    { name: 'Roles', href: '/admin/roles', icon: Cog6ToothIcon, permission: '*' },
    { name: 'Audit Logs', href: '/admin/audit-logs', icon: ClipboardDocumentListIcon, permission: '*' },
];

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

export default function Sidebar({ collapsed = false }) {
    const { url, props } = usePage();
    const { auth } = props;
    const permissions = auth?.user?.permissions || [];

    const hasPermission = (permission) => {
        if (permission === '*') return true;
        return permissions.includes('*') || permissions.includes(permission);
    };

    const isActive = (href) => {
        return url.startsWith(href);
    };

    return (
        <div className={classNames(
            'flex flex-col bg-gray-900 border-r border-gray-800 transition-all duration-300',
            collapsed ? 'w-16' : 'w-64'
        )}>
            {/* Logo */}
            <div className="flex items-center justify-center h-16 bg-gray-800 border-b border-gray-700">
                {!collapsed && (
                    <span className="text-white font-bold text-xl">AirPanas Admin</span>
                )}
                {collapsed && (
                    <span className="text-white font-bold text-xl">AP</span>
                )}
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                {navigation.map((item) => {
                    // Skip items user doesn't have permission for
                    if (item.permission && !hasPermission(item.permission)) {
                        return null;
                    }

                    if (item.children) {
                        // Collapsible menu
                        return (
                            <div key={item.name} className="space-y-1">
                                <div className={classNames(
                                    'flex items-center px-3 py-2 text-sm font-medium rounded-md',
                                    'text-gray-300 hover:bg-gray-800'
                                )}>
                                    <item.icon className="h-5 w-5 mr-3" />
                                    {!collapsed && <span>{item.name}</span>}
                                </div>
                                {!collapsed && (
                                    <div className="ml-4 space-y-1">
                                        {item.children.map((child) => {
                                            if (child.permission && !hasPermission(child.permission)) {
                                                return null;
                                            }
                                            return (
                                                <Link
                                                    key={child.name}
                                                    href={child.href}
                                                    className={classNames(
                                                        'flex items-center px-3 py-2 text-sm font-medium rounded-md',
                                                        isActive(child.href)
                                                            ? 'bg-gray-800 text-white'
                                                            : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                                                    )}
                                                >
                                                    {child.name}
                                                </Link>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        );
                    }

                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={classNames(
                                'flex items-center px-3 py-2 text-sm font-medium rounded-md',
                                isActive(item.href)
                                    ? 'bg-gray-800 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            )}
                        >
                            <item.icon className="h-5 w-5" />
                            {!collapsed && <span className="ml-3">{item.name}</span>}
                        </Link>
                    );
                })}
            </nav>

            {/* User info */}
            {!collapsed && auth?.user && (
                <div className="p-4 border-t border-gray-800">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <div className="h-8 w-8 rounded-full bg-gray-700 flex items-center justify-center">
                                <span className="text-white text-sm font-medium">
                                    {auth.user.full_name?.charAt(0).toUpperCase()}
                                </span>
                            </div>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm font-medium text-white">{auth.user.full_name}</p>
                            <p className="text-xs text-gray-400">{auth.user.role_name}</p>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
