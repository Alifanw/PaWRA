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
    ClipboardDocumentCheckIcon,
    ChevronDownIcon,
} from '@heroicons/react/24/outline';
import { useState, useEffect } from 'react';

const navigation = [
    { name: 'Dashboard', href: '/admin/dashboard', icon: HomeIcon, roles: ['*'] },
    { name: 'Ticket Sales', href: '/admin/ticket-sales', icon: TicketIcon, roles: ['ticketing', 'superadmin'] },
    { name: 'Bookings', href: '/admin/bookings', icon: CalendarDaysIcon, roles: ['booking', 'superadmin'] },
    { name: 'Parking', href: '/admin/parking', icon: BuildingStorefrontIcon, roles: ['parking', 'superadmin'] },
    { name: 'Products', href: '/admin/products', icon: BuildingStorefrontIcon, roles: ['superadmin', 'monitoring'] },
    { name: 'Product Codes', href: '/admin/product-codes', icon: ClipboardDocumentCheckIcon, roles: ['superadmin', 'monitoring'] },
    { name: 'Users', href: '/admin/users', icon: UsersIcon, roles: ['superadmin', 'admin'] },
    { name: 'Roles', href: '/admin/roles', icon: Cog6ToothIcon, roles: ['superadmin', 'admin'] },
    {
        name: 'Reports',
        icon: ChartBarIcon,
        roles: ['superadmin', 'monitoring'],
        children: [
            { name: 'All Transactions', href: '/admin/reports/all-transactions', roles: ['superadmin', 'monitoring'] },
        ],
    },
    { name: 'Audit Logs', href: '/admin/audit-logs', icon: ClipboardDocumentListIcon, roles: ['superadmin', 'monitoring'] },
    { name: 'Attendance', href: '/admin/attendance', icon: ClipboardDocumentCheckIcon, roles: ['superadmin', 'monitoring'] },
];

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

export default function Sidebar({ collapsed = false, mobileOpen = false, onCloseMobile = () => {} }) {
    const { url, props } = usePage();
    const { auth } = props;
    const userRoles = auth?.user?.roles || [];
    
    // Extract role names from role objects or use string names directly
    const roleNames = userRoles.map(role => {
        if (typeof role === 'string') {
            return role;
        }
        return role.name;
    });

    // Debug: Log user roles
    useEffect(() => {
        console.log('🔐 Sidebar Debug - User Roles:', userRoles);
        console.log('🔐 Extracted role names:', roleNames);
        console.log('🔐 Auth object:', auth?.user?.name);
    }, [userRoles, roleNames, auth]);

    // track which parent menus are open (for animated dropdowns)
    const [openMenus, setOpenMenus] = useState({});

    const toggleMenu = (name) => {
        setOpenMenus((s) => ({ ...s, [name]: !s[name] }));
    };

    // Close open menus when the current URL changes (navigation happened)
    useEffect(() => {
        setOpenMenus({});
    }, [url]);

    const hasAccess = (itemRoles) => {
        // Debug each check
        console.log(`Checking access for roles:`, itemRoles, `User roles:`, roleNames);
        
        // Allow if no specific roles required or wildcard
        if (!itemRoles || itemRoles.length === 0 || itemRoles.includes('*')) {
            console.log('→ Allowed (wildcard or no requirement)');
            return true;
        }
        
        // Deny if user has no roles
        if (!roleNames || !Array.isArray(roleNames) || roleNames.length === 0) {
            console.log('→ Denied (user has no roles)');
            return false;
        }
        
        // Check if user has ANY of the required roles (case-insensitive)
        const hasRole = itemRoles.some(role => {
            const roleMatches = roleNames.some(userRole => 
                userRole.toLowerCase() === role.toLowerCase()
            );
            console.log(`  Checking "${role}" in [${roleNames.join(', ')}]: ${roleMatches}`);
            return roleMatches;
        });
        console.log(`→ ${hasRole ? 'Allowed' : 'Denied'} (role match:`, hasRole, ')');
        return hasRole;
    };

    const isActive = (href) => {
        return url.startsWith(href);
    };

    return (
        <>
            {/* Mobile backdrop */}
            <div
                className={classNames(
                    'fixed inset-0 bg-black/40 z-40 transition-opacity lg:hidden',
                    mobileOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'
                )}
                onClick={() => onCloseMobile()}
            />

            <div className={classNames(
                // mobile: fixed overlay that slides in/out; desktop: static sidebar with width toggle
                'fixed inset-y-0 left-0 z-50 transform transition-transform lg:static lg:translate-x-0',
                mobileOpen ? 'translate-x-0' : '-translate-x-full',
                collapsed ? 'lg:w-16' : 'lg:w-64',
                'w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-700'
            )}>
            {/* Logo */}
            <div className="flex items-center justify-center h-16 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                {!collapsed && (
                    <span className="text-blue-600 dark:text-blue-400 font-bold text-xl">AirPanas Walini</span>
                )}
                {collapsed && ( 
                    <span className="text-blue-600 dark:text-blue-400 font-bold text-xl">AW</span>
                )}
            </div>

            {/* Navigation */}
            <nav className="flex-1 px-2 py-4 space-y-1 overflow-y-auto">
                {navigation.map((item) => {
                    // Skip items user doesn't have access to
                    if (!hasAccess(item.roles)) {
                        return null;
                    }

                    if (item.children) {
                        // Collapsible menu with animation
                        const isOpen = !!openMenus[item.name];
                        return (
                            <div key={item.name} className="space-y-1">
                                <button
                                    type="button"
                                    onClick={() => toggleMenu(item.name)}
                                    className={classNames(
                                        'w-full flex items-center px-3 py-2 text-sm font-medium rounded-md justify-between',
                                        'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors'
                                    )}
                                >
                                    <div className="flex items-center">
                                        <item.icon className="h-5 w-5 mr-3" />
                                        {!collapsed && <span>{item.name}</span>}
                                    </div>
                                    {!collapsed && (
                                        <ChevronDownIcon className={classNames('h-4 w-4 transition-transform', isOpen ? 'rotate-180' : 'rotate-0')} />
                                    )}
                                </button>

                                {/* Animated children container */}
                                {!collapsed && (
                                    <div className={classNames(
                                        'ml-4 overflow-hidden transition-all duration-300',
                                        isOpen ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0'
                                    )}>
                                        <div className="space-y-1">
                                            {item.children.map((child) => {
                                                if (!hasAccess(child.roles)) {
                                                    return null;
                                                }
                                                return (
                                                    <Link
                                                        key={child.name}
                                                        href={child.href}
                                                        onClick={() => {
                                                            // collapse submenu immediately and notify layout to close on small screens
                                                            setOpenMenus({});
                                                            if (typeof window !== 'undefined') {
                                                                window.dispatchEvent(new CustomEvent('sidebar:close'));
                                                            }
                                                        }}
                                                        className={classNames(
                                                            'flex items-center px-3 py-2 text-sm font-medium rounded-md',
                                                            isActive(child.href)
                                                                ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300'
                                                                : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors'
                                                        )}
                                                    >
                                                        {child.name}
                                                    </Link>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}
                            </div>
                        );
                    }

                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            onClick={() => {
                                // close any open submenus and notify layout to close sidebar on small screens
                                setOpenMenus({});
                                if (typeof window !== 'undefined') {
                                    window.dispatchEvent(new CustomEvent('sidebar:close'));
                                }
                            }}
                            className={classNames(
                                'flex items-center px-3 py-2 text-sm font-medium rounded-md',
                                isActive(item.href)
                                    ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors'
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
                <div className="p-4 border-t border-slate-200 dark:border-slate-700">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <div className="h-8 w-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                <span className="text-blue-600 dark:text-blue-300 text-sm font-medium">
                                    {auth.user.full_name?.charAt(0).toUpperCase()}
                                </span>
                            </div>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm font-medium text-slate-900 dark:text-slate-100">{auth.user.full_name}</p>
                            <p className="text-xs text-slate-500 dark:text-slate-400">{auth.user.role_name}</p>
                        </div>
                    </div>
                </div>
            )}
            </div>
        </>
    );
}
