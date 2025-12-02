import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { useState } from 'react';

import ReportDateExport from './ReportDateExport';

export default function BookingReport({ auth, bookings, summary, filters }) {
    return (
        <AdminLayout auth={auth} title="Booking Reports">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold dark:text-slate-100">Booking Reports</h1>
                <p className="text-sm text-slate-600 dark:text-slate-400">View booking statistics and reports</p>
            </div>
            <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow mb-6">
                <ReportDateExport
                    filters={filters}
                    onFilterChange={(newFilters) => router.get(route('admin.reports.bookings'), newFilters, {preserveState:true})}
                />
            </div>
            <div className="grid grid-cols-3 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Total Bookings</div><div className="text-2xl font-bold dark:text-slate-100">{summary.total_bookings}</div></div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Confirmed</div><div className="text-2xl font-bold text-green-600 dark:text-green-400">{summary.confirmed}</div></div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Revenue</div><div className="text-2xl font-bold text-blue-600 dark:text-blue-400">Rp {Number(summary.total_revenue).toLocaleString('id-ID')}</div></div>
            </div>
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead className="bg-slate-50 dark:bg-slate-700">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Code</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Customer</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Check In</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Nights</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Amount</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        {bookings.map(b => (
                            <tr key={b.booking_code}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{b.booking_code}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{b.customer_name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{b.checkin}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{b.night_count}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">Rp {Number(b.total_amount).toLocaleString('id-ID')}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{b.status}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
