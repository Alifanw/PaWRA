import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';

export default function BookingReport({ auth, bookings, summary, filters }) {
    return (
        <AdminLayout auth={auth} title="Booking Reports">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">Booking Reports</h1>
                <p className="text-sm text-gray-600">View booking statistics and reports</p>
            </div>
            <div className="bg-white p-4 rounded-lg shadow mb-6">
                <div className="grid grid-cols-2 gap-4">
                    <input type="date" defaultValue={filters.startDate} onChange={e => router.get(route('admin.reports.bookings'), {...filters, start_date: e.target.value}, {preserveState:true})} className="rounded-md border-gray-300" />
                    <input type="date" defaultValue={filters.endDate} onChange={e => router.get(route('admin.reports.bookings'), {...filters, end_date: e.target.value}, {preserveState:true})} className="rounded-md border-gray-300" />
                </div>
            </div>
            <div className="grid grid-cols-3 gap-4 mb-6">
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Total Bookings</div><div className="text-2xl font-bold">{summary.total_bookings}</div></div>
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Confirmed</div><div className="text-2xl font-bold text-green-600">{summary.confirmed}</div></div>
                <div className="bg-white p-4 rounded-lg shadow"><div className="text-gray-500 text-sm">Revenue</div><div className="text-2xl font-bold text-blue-600">Rp {Number(summary.total_revenue).toLocaleString('id-ID')}</div></div>
            </div>
            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nights</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y">
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
