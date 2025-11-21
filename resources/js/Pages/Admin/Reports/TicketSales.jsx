import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';

export default function TicketSalesReport({ auth, sales, dailySales, summary, filters }) {
    return (
        <AdminLayout auth={auth} title="Ticket Sales Reports">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">Ticket Sales Reports</h1>
                <p className="text-sm text-gray-600">View sales statistics and reports</p>
            </div>
            <div className="bg-white p-4 rounded-lg shadow mb-6">
                <div className="grid grid-cols-2 gap-4">
                    <input type="date" defaultValue={filters.startDate} onChange={e => router.get(route('admin.reports.ticket-sales'), {...filters, start_date: e.target.value}, {preserveState:true})} className="rounded-md border-gray-300" />
                    <input type="date" defaultValue={filters.endDate} onChange={e => router.get(route('admin.reports.ticket-sales'), {...filters, end_date: e.target.value}, {preserveState:true})} className="rounded-md border-gray-300" />
                </div>
            </div>
            <div className="grid grid-cols-4 gap-4 mb-6">
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Transactions</div><div className="text-2xl font-bold">{summary.total_transactions}</div></div>
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Total Qty</div><div className="text-2xl font-bold">{summary.total_qty}</div></div>
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Discount</div><div className="text-2xl font-bold text-orange-600">Rp {Number(summary.total_discount).toLocaleString('id-ID')}</div></div>
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Revenue</div><div className="text-2xl font-bold text-green-600">Rp {Number(summary.total_revenue).toLocaleString('id-ID')}</div></div>
            </div>
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cashier</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gross</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Net</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y">
                        {sales.map(s => (
                            <tr key={s.invoice_no}>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.invoice_no}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.sale_date}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.cashier_name}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">{s.total_qty}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">Rp {Number(s.gross_amount).toLocaleString('id-ID')}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm">Rp {Number(s.discount_amount).toLocaleString('id-ID')}</td>
                                <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold">Rp {Number(s.net_amount).toLocaleString('id-ID')}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
