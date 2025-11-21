import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import Modal from '@/Components/Modal';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import toast from 'react-hot-toast';

export default function RoleIndex({ auth, roles, filters, availablePermissions }) {
    const [showModal, setShowModal] = useState(false);
    const [editingRole, setEditingRole] = useState(null);
    const { data, setData, post, put, processing, errors, reset } = useForm({ name: '', description: '', permissions: [] });

    const columns = [
        { header: 'Name', accessorKey: 'name' },
        { header: 'Description', accessorKey: 'description' },
        { header: 'Users', accessorKey: 'users_count' },
        { header: 'Created', accessorKey: 'created_at' },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex space-x-2">
                    <button onClick={() => handleEdit(row.original)} className="text-blue-600"><PencilIcon className="h-5 w-5" /></button>
                    <button onClick={() => handleDelete(row.original.id)} className="text-red-600"><TrashIcon className="h-5 w-5" /></button>
                </div>
            ),
        },
    ];

    const handleEdit = (role) => {
        setEditingRole(role);
        router.get(route('admin.roles.permissions', role.id), {}, {
            onSuccess: (page) => {
                setData({ name: role.name, description: role.description, permissions: page.props.permissions || [] });
                setShowModal(true);
            }
        });
    };

    const handleDelete = (id) => {
        if (confirm('Delete this role?')) {
            router.delete(route('admin.roles.destroy', id), {
                onSuccess: () => toast.success('Role deleted'),
                onError: (errors) => toast.error(errors.error || 'Failed'),
            });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const options = {
            onSuccess: () => { toast.success(editingRole ? 'Role updated' : 'Role created'); closeModal(); },
            onError: () => toast.error('Failed'),
        };
        editingRole ? put(route('admin.roles.update', editingRole.id), options) : post(route('admin.roles.store'), options);
    };

    const closeModal = () => { setShowModal(false); setEditingRole(null); reset(); };

    const togglePermission = (perm) => {
        setData('permissions', data.permissions.includes(perm) ? data.permissions.filter(p => p !== perm) : [...data.permissions, perm]);
    };

    return (
        <AdminLayout auth={auth} title="Roles">
            <div className="mb-6 flex justify-between items-center">
                <div><h1 className="text-2xl font-semibold">Roles</h1><p className="text-sm text-gray-600">Manage user roles</p></div>
                <button onClick={() => { setEditingRole(null); reset(); setShowModal(true); }} className="inline-flex items-center px-4 py-2 text-sm rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <PlusIcon className="-ml-1 mr-2 h-5 w-5" />Add Role
                </button>
            </div>
            <DataTable columns={columns} data={roles.data} pagination={roles} />
            <Modal show={showModal} onClose={closeModal} maxWidth="3xl">
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <h2 className="text-xl font-semibold">{editingRole ? 'Edit' : 'Add'} Role</h2>
                    <input type="text" value={data.name} onChange={e => setData('name', e.target.value)} placeholder="Role Name" className="w-full rounded-md border-gray-300" required />
                    <textarea value={data.description} onChange={e => setData('description', e.target.value)} placeholder="Description" className="w-full rounded-md border-gray-300" rows={2} />
                    <div>
                        <label className="block text-sm font-medium mb-2">Permissions</label>
                        <div className="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto border rounded p-3">
                            {availablePermissions?.map(perm => (
                                <label key={perm} className="flex items-center">
                                    <input type="checkbox" checked={data.permissions.includes(perm)} onChange={() => togglePermission(perm)} className="rounded" />
                                    <span className="ml-2 text-sm">{perm}</span>
                                </label>
                            ))}
                        </div>
                    </div>
                    <div className="flex justify-end space-x-3 pt-4">
                        <button type="button" onClick={closeModal} className="px-4 py-2 border rounded-md">Cancel</button>
                        <button type="submit" disabled={processing} className="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">{processing ? 'Saving...' : (editingRole ? 'Update' : 'Create')}</button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
