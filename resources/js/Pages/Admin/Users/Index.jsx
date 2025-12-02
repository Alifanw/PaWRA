import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import Modal from '@/Components/Modal';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import toast from 'react-hot-toast';

export default function UserIndex({ auth, users, roles, filters }) {
    const [showModal, setShowModal] = useState(false);
    const [editingUser, setEditingUser] = useState(null);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        role_id: '',
        is_active: true,
    });

    const columns = [
        { header: 'Name', accessorKey: 'name' },
        { header: 'Username', accessorKey: 'username' },
        { header: 'Email', accessorKey: 'email' },
        { header: 'Role', accessorKey: 'role_name' },
        {
            header: 'Status',
            accessorKey: 'is_active',
            cell: ({ row }) => (
                <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                    row.original.is_active ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
                }`}>
                    {row.original.is_active ? 'Active' : 'Inactive'}
                </span>
            ),
        },
        { header: 'Created', accessorKey: 'created_at' },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex space-x-2">
                    <button onClick={() => handleEdit(row.original)} className="text-blue-600 hover:text-blue-900">
                        <PencilIcon className="h-5 w-5" />
                    </button>
                    <button onClick={() => handleDelete(row.original.id)} className="text-red-600 hover:text-red-900">
                        <TrashIcon className="h-5 w-5" />
                    </button>
                </div>
            ),
        },
    ];

    const handleEdit = (user) => {
        setEditingUser(user);
        setData({ name: user.name, username: user.username, email: user.email, password: '', role_id: user.role_id || '', is_active: user.is_active });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (confirm('Delete this user?')) {
            router.delete(route('admin.users.destroy', id), {
                onSuccess: () => toast.success('User deleted'),
                onError: (errors) => toast.error(errors.error || 'Failed'),
            });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const options = {
            onSuccess: () => { toast.success(editingUser ? 'User updated' : 'User created'); closeModal(); },
            onError: () => toast.error('Failed to save'),
        };
        editingUser ? put(route('admin.users.update', editingUser.id), options) : post(route('admin.users.store'), options);
    };

    const closeModal = () => { setShowModal(false); setEditingUser(null); reset(); };

    return (
        <AdminLayout auth={auth} title="Users">
            <div className="mb-6 flex justify-between items-center">
                <div><h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Users</h1><p className="text-sm text-slate-600 dark:text-slate-400">Manage system users</p></div>
                <button onClick={() => { setEditingUser(null); reset(); setShowModal(true); }} className="inline-flex items-center px-4 py-2 border-0 text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <PlusIcon className="-ml-1 mr-2 h-5 w-5" />Add User
                </button>
            </div>
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-4 mb-6 border border-slate-200 dark:border-slate-700">
                <div className="grid grid-cols-2 gap-4">
                    <input type="text" defaultValue={filters?.search} onChange={(e) => router.get(route('admin.users.index'), {...filters, search: e.target.value}, {preserveState:true,replace:true})} placeholder="Search..." className="rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" />
                    <select defaultValue={filters?.role_id || ''} onChange={(e) => router.get(route('admin.users.index'), {...filters, role_id: e.target.value}, {preserveState:true,replace:true})} className="rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                        <option value="">All Roles</option>
                        {roles?.map(r => <option key={r.id} value={r.id}>{r.name}</option>)}
                    </select>
                </div>
            </div>
            <DataTable columns={columns} data={users.data} pagination={users} />
            <Modal show={showModal} onClose={closeModal} maxWidth="2xl">
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <h2 className="text-xl font-semibold text-slate-900 dark:text-slate-100">{editingUser ? 'Edit' : 'Add'} User</h2>
                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} placeholder="Name" className="w-full rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" required />
                    <div className="grid grid-cols-2 gap-4">
                        <input type="text" value={data.username} onChange={e => setData('username', e.target.value)} placeholder="Username" className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" required />
                        <input type="email" value={data.email} onChange={e => setData('email', e.target.value)} placeholder="Email" className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" required />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <input type="password" value={data.password} onChange={e => setData('password', e.target.value)} placeholder={editingUser ? 'New Password (optional)' : 'Password'} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" required={!editingUser} minLength="8" />
                        <select value={data.role_id} onChange={e => setData('role_id', e.target.value)} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" required>
                            <option value="">Select Role</option>
                            {roles?.map(r => <option key={r.id} value={r.id}>{r.name}</option>)}
                        </select>
                    </div>
                    <label className="flex items-center"><input type="checkbox" checked={data.is_active} onChange={e => setData('is_active', e.target.checked)} className="rounded dark:bg-slate-700 dark:border-slate-600" /> <span className="ml-2 dark:text-slate-100">Active</span></label>
                    <div className="flex justify-end space-x-3 pt-4">
                        <button type="button" onClick={closeModal} className="px-4 py-2 border border-slate-300 rounded-md text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 dark:border-slate-600">Cancel</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50">{processing ? 'Saving...' : (editingUser ? 'Update' : 'Create')}</button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
