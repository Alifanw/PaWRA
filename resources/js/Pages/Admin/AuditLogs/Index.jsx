import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import { router } from '@inertiajs/react';

export default function AuditLogIndex({ auth, logs, actions, filters }) {
    const columns = [
        { header: 'Date', accessorKey: 'created_at' },
        { header: 'Action', accessorKey: 'action' },
        { header: 'Resource', accessorKey: 'resource' },
        { header: 'ID', accessorKey: 'resource_id' },
        { header: 'User', accessorKey: 'user_name' },
        { header: 'IP Address', accessorKey: 'ip_address' },
    ];

    return (
        <AdminLayout auth={auth} title="Audit Logs">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold dark:text-slate-100">Audit Logs</h1>
                <p className="text-sm text-slate-600 dark:text-slate-400">System activity audit trail</p>
            </div>
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-4 mb-6">
                <div className="grid grid-cols-4 gap-4">
                    <div>
                        <label htmlFor="audit-search" className="sr-only">Search</label>
                        <input id="audit-search" name="search" type="text" defaultValue={filters?.search} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, search: e.target.value}, {preserveState:true,replace:true})} placeholder="Search..." className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" />
                    </div>
                    <div>
                        <label htmlFor="audit-action" className="sr-only">Action</label>
                        <select id="audit-action" name="action" defaultValue={filters?.action || ''} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, action: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600">
                        <option value="">All Actions</option>
                        {actions?.map(a => <option key={a} value={a}>{a}</option>)}
                    </select>
                    </div>
                    <div>
                        <label htmlFor="audit-start-date" className="sr-only">Start Date</label>
                        <input id="audit-start-date" name="start_date" type="date" defaultValue={filters?.start_date} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, start_date: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" />
                    </div>
                    <div>
                        <label htmlFor="audit-end-date" className="sr-only">End Date</label>
                        <input id="audit-end-date" name="end_date" type="date" defaultValue={filters?.end_date} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, end_date: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" />
                    </div>
                </div>
            </div>
            <DataTable columns={columns} data={logs.data} pagination={logs} routeName="admin.audit-logs.index" filters={filters} />
        </AdminLayout>
    );
}
