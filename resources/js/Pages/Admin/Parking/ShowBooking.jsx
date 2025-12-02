import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

export default function ShowBooking({ auth, booking }) {
    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Booking {booking.booking_code}</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Detail booking parkir</p>
                    </div>
                    <div className="flex gap-2">
                        <a href={`/admin/parking/bookings/${booking.id}/print`} target="_blank" className="inline-flex items-center px-4 py-2 rounded-md bg-slate-700 text-white">Print</a>
                        <Link href="/admin/parking/bookings" className="inline-flex items-center px-4 py-2 rounded-md bg-slate-100">Back</Link>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow max-w-2xl">
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <h3 className="text-sm text-slate-500">Booking Code</h3>
                        <div className="font-medium">{booking.booking_code}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Date</h3>
                        <div className="font-medium">{new Date(booking.created_at).toLocaleString()}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Customer</h3>
                        <div className="font-medium">{booking.customer_name}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Lot</h3>
                        <div className="font-medium">{booking.parking_lot}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Period</h3>
                        <div className="font-medium">{booking.start_time} - {booking.end_time}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Price</h3>
                        <div className="font-medium">Rp {Number(booking.price).toLocaleString('id-ID')}</div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
