import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

export default function Bookings({ auth, bookings }) {
    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Parking Bookings</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Manage parking bookings</p>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead>
                            <tr>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Code</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Customer</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Lot</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Start</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">End</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Price</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Status</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                            {bookings.data?.map((b) => (
                                <tr key={b.id}>
                                    <td className="px-4 py-3">{b.booking_code}</td>
                                    <td className="px-4 py-3">{b.customer_name}</td>
                                    <td className="px-4 py-3">{b.parking_lot}</td>
                                    <td className="px-4 py-3">{new Date(b.start_time).toLocaleString()}</td>
                                    <td className="px-4 py-3">{new Date(b.end_time).toLocaleString()}</td>
                                    <td className="px-4 py-3">Rp {Number(b.price).toLocaleString('id-ID')}</td>
                                    <td className="px-4 py-3">{b.status}</td>
                                    <td className="px-4 py-3">
                                        <Link href={`/admin/parking/bookings/${b.id}`} className="text-sm text-blue-600">View</Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="mt-4">
                    {bookings.prev_page_url && (<Link href={bookings.prev_page_url} className="px-3 py-1 bg-slate-100 rounded">Prev</Link>)}
                    {bookings.next_page_url && (<Link href={bookings.next_page_url} className="px-3 py-1 bg-slate-100 rounded">Next</Link>)}
                </div>
            </div>
        </AdminLayout>
    );
}
