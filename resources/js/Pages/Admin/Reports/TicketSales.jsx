import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { useState } from 'react';

import ReportDateExport from './ReportDateExport';

export default function TicketSalesReport({ auth, sales, dailySales, summary, filters }) {
    return (
        <AdminLayout auth={auth} title="Ticket Sales Reports">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold dark:text-slate-100">Ticket Sales Reports</h1>
                <p className="text-sm text-slate-600 dark:text-slate-400">View sales statistics and reports</p>
            </div>
            <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow mb-6">
                <ReportDateExport
                    filters={filters}
                    onFilterChange={(newFilters) => router.get(route('admin.reports.ticket-sales'), newFilters, {preserveState:true})}
                />
            </div>
            <div className="grid grid-cols-4 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Transactions</div><div className="text-2xl font-bold dark:text-slate-100">{summary.total_transactions}</div></div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Total Qty</div><div className="text-2xl font-bold dark:text-slate-100">{summary.total_qty}</div></div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Discount</div><div className="text-2xl font-bold text-orange-600 dark:text-orange-400">Rp {Number(summary.total_discount).toLocaleString('id-ID')}</div></div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow"><div className="text-slate-500 dark:text-slate-400 text-sm">Revenue</div><div className="text-2xl font-bold text-green-600 dark:text-green-400">Rp {Number(summary.total_revenue).toLocaleString('id-ID')}</div></div>
            </div>
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead className="bg-slate-50 dark:bg-slate-700">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Invoice</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Date</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Cashier</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Qty</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Gross</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Discount</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Net</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        {sales.map(s => (
                            <tr key={s.invoice_no}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.invoice_no}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.sale_date}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.cashier_name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.total_qty}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">Rp {Number(s.gross_amount).toLocaleString('id-ID')}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">Rp {Number(s.discount_amount).toLocaleString('id-ID')}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-slate-100">Rp {Number(s.net_amount).toLocaleString('id-ID')}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
