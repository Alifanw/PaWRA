import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

export default function ShowTransaction({ auth, transaction }) {
    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Transaksi {transaction.transaction_code}</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Detail transaksi parkir</p>
                    </div>
                    <div className="flex gap-2">
                        <a href={`/admin/parking/transactions/${transaction.id}/print`} target="_blank" className="inline-flex items-center px-4 py-2 rounded-md bg-slate-700 text-white">Print</a>
                        <Link href="/admin/parking" className="inline-flex items-center px-4 py-2 rounded-md bg-slate-100">Back</Link>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow max-w-2xl">
                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <h3 className="text-sm text-slate-500">Transaction Code</h3>
                        <div className="font-medium">{transaction.transaction_code}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Date</h3>
                        <div className="font-medium">{new Date(transaction.created_at).toLocaleString()}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Vehicle</h3>
                        <div className="font-medium">{transaction.vehicle_number || '-'}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Type</h3>
                        <div className="font-medium">{transaction.vehicle_type === 'roda4_6' ? 'Roda 4 & 6' : 'Roda 2'}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Count</h3>
                        <div className="font-medium">{transaction.vehicle_count}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">Amount</h3>
                        <div className="font-medium">Rp {Number(transaction.total_amount).toLocaleString('id-ID')}</div>
                    </div>
                    <div>
                        <h3 className="text-sm text-slate-500">By</h3>
                        <div className="font-medium">{transaction.created_by_name}</div>
                    </div>
                </div>

                {transaction.notes && (
                    <div className="mt-4">
                        <h3 className="text-sm text-slate-500">Notes</h3>
                        <div className="font-medium">{transaction.notes}</div>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
