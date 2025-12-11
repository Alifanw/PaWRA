import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

export default function Index({ auth, transactions, filters }) {
    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Parking Transactions</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">List of recent parking transactions</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href="/admin/parking/create" className="inline-flex items-center px-4 py-2 rounded-md bg-slate-700 text-white">New Transaction</Link>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead>
                            <tr>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Code</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Vehicle</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Type</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Count</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Amount</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Status</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">By</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Date</th>
                                <th className="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                            {transactions.data?.map((t) => (
                                <tr key={t.id}>
                                    <td className="px-4 py-3">{t.transaction_code}</td>
                                    <td className="px-4 py-3">{t.vehicle_number || '-'}</td>
                                    <td className="px-4 py-3">{t.vehicle_type === 'roda4_6' ? 'Roda 4 & 6' : 'Roda 2'}</td>
                                    <td className="px-4 py-3">{t.vehicle_count}</td>
                                    <td className="px-4 py-3">Rp {Number(t.total_amount).toLocaleString('id-ID')}</td>
                                    <td className="px-4 py-3">{t.status}</td>
                                    <td className="px-4 py-3">{t.created_by_name}</td>
                                    <td className="px-4 py-3">{new Date(t.created_at).toLocaleString()}</td>
                                    <td className="px-4 py-3">
                                        <Link href={`/admin/parking/transactions/${t.id}`} className="text-sm text-blue-600">View</Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="mt-4">
                    {/* basic pagination controls provided by server-side */}
                    {transactions.links && (
                        <div className="flex items-center gap-2 text-sm text-slate-600">
                            {/* Render simple previous/next links if provided */}
                            {transactions.prev_page_url && (<Link href={transactions.prev_page_url} className="px-3 py-1 bg-slate-100 rounded">Prev</Link>)}
                            {transactions.next_page_url && (<Link href={transactions.next_page_url} className="px-3 py-1 bg-slate-100 rounded">Next</Link>)}
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
