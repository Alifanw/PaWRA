import { Fragment } from 'react';
import { Menu, Transition } from '@headlessui/react';
import { Link, router } from '@inertiajs/react';
import { Bars3Icon, UserCircleIcon } from '@heroicons/react/24/outline';

function classNames(...classes) {
    return classes.filter(Boolean).join(' ');
}

// no local state required in Topbar

export default function Topbar({ onToggleSidebar, auth }) {
    const handleLogout = () => {
        router.post('/logout');
    };


    return (
        <div className="flex h-16 shrink-0 items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
            {/* Sidebar toggle */}
            <button
                type="button"
                onClick={onToggleSidebar}
                className="p-2.5 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
            >
                <span className="sr-only">Toggle sidebar</span>
                <Bars3Icon className="h-6 w-6" aria-hidden="true" />
            </button>

            {/* Separator */}
            <div className="h-6 w-px bg-slate-200 dark:bg-slate-700" aria-hidden="true" />

            <div className="ml-auto">
                {/* Profile dropdown */}
                <Menu as="div" className="relative">
                    <Menu.Button className="flex items-center p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors">
                        <span className="sr-only">Open user menu</span>
                        <div className="h-8 w-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                            <UserCircleIcon className="h-6 w-6 text-slate-600 dark:text-slate-300" />
                        </div>
                        <span className="hidden lg:flex lg:items-center">
                            <span
                                className="ml-4 text-sm font-semibold leading-6 text-slate-900 dark:text-slate-100"
                                aria-hidden="true"
                            >
                                {auth?.user?.full_name}
                            </span>
                        </span>
                    </Menu.Button>
                        <Transition
                            as={Fragment}
                            enter="transition ease-out duration-100"
                            enterFrom="transform opacity-0 scale-95"
                            enterTo="transform opacity-100 scale-100"
                            leave="transition ease-in duration-75"
                            leaveFrom="transform opacity-100 scale-100"
                            leaveTo="transform opacity-0 scale-95"
                        >
                            <Menu.Items className="absolute right-0 z-10 mt-2.5 w-48 origin-top-right rounded-md bg-white dark:bg-slate-900 py-2 shadow-lg ring-1 ring-slate-900/5 dark:ring-white/5 focus:outline-none">
                           
                            <Menu.Item>
                                {({ active }) => (
                                    <button
                                        onClick={handleLogout}
                                        className={classNames(
                                            active ? 'bg-slate-50 dark:bg-slate-800' : '',
                                            'block w-full text-left px-3 py-1 text-sm leading-6 text-slate-900 dark:text-slate-100'
                                        )}
                                    >
                                        Sign out
                                    </button>
                                )}
                            </Menu.Item>
                        </Menu.Items>
                    </Transition>
                </Menu>
            </div>
        </div>
    );
}
