import AdminLayout from '@/Layouts/AdminLayout';

export default function Monitor({ auth, monitors }) {
    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Parking Monitor</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Recent monitoring events</p>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead>
                            <tr>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Action</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Status</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Meta</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">By</th>
                                <th className="px-4 py-2 text-left text-sm text-slate-500">Date</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                            {monitors.data?.map((m) => (
                                <tr key={m.id}>
                                    <td className="px-4 py-3">{m.action}</td>
                                    <td className="px-4 py-3">{m.status}</td>
                                    <td className="px-4 py-3">{m.meta || '-'}</td>
                                    <td className="px-4 py-3">{m.created_by_name}</td>
                                    <td className="px-4 py-3">{new Date(m.created_at).toLocaleString()}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <div className="mt-4">
                    {monitors.prev_page_url && (<a href={monitors.prev_page_url} className="px-3 py-1 bg-slate-100 rounded">Prev</a>)}
                    {monitors.next_page_url && (<a href={monitors.next_page_url} className="px-3 py-1 bg-slate-100 rounded">Next</a>)}
                </div>
            </div>
        </AdminLayout>
    );
}
