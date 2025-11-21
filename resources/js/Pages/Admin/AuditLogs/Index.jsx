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
                <h1 className="text-2xl font-semibold">Audit Logs</h1>
                <p className="text-sm text-gray-600">System activity audit trail</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4 mb-6">
                <div className="grid grid-cols-4 gap-4">
                    <input type="text" defaultValue={filters?.search} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, search: e.target.value}, {preserveState:true,replace:true})} placeholder="Search..." className="rounded-md border-gray-300" />
                    <select defaultValue={filters?.action || ''} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, action: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-gray-300">
                        <option value="">All Actions</option>
                        {actions?.map(a => <option key={a} value={a}>{a}</option>)}
                    </select>
                    <input type="date" defaultValue={filters?.start_date} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, start_date: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-gray-300" />
                    <input type="date" defaultValue={filters?.end_date} onChange={e => router.get(route('admin.audit-logs.index'), {...filters, end_date: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-gray-300" />
                </div>
            </div>
            <DataTable columns={columns} data={logs.data} pagination={logs} />
        </AdminLayout>
    );
}
