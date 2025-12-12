import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import BulkActionsToolbar from '@/Components/Admin/BulkActionsToolbar';
import Modal from '@/Components/Modal';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { useBulkSelection } from '@/hooks/useBulkSelection';
import toast from 'react-hot-toast';

export default function ProductCodeIndex({ auth, productCodes, products, filters }) {
    const [showModal, setShowModal] = useState(false);
    const [editingCode, setEditingCode] = useState(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const { selectedIds, selectAllChecked, toggleSelection, toggleSelectAll, clearSelection, isSelected } = useBulkSelection();

    const { data, setData, post, put, processing, errors, reset } = useForm({
        product_id: '',
        code: '',
        status: 'available',
        notes: '',
    });

    const columns = [
        {
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={selectAllChecked}
                    onChange={() => toggleSelectAll(productCodes.data || [])}
                    className="rounded dark:bg-slate-700 dark:border-slate-600"
                />
            ),
            accessorKey: 'checkbox',
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={isSelected(row.original.id)}
                    onChange={() => toggleSelection(row.original.id)}
                    className="rounded dark:bg-slate-700 dark:border-slate-600"
                />
            ),
            size: 50,
        },
        {
            header: 'Code',
            accessorKey: 'code',
        },
        {
            header: 'Product',
            accessorKey: 'product_name',
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const statusColors = {
                    'available': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                    'unavailable': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                    'maintenance': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                };
                return (
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[row.original.status] || ''}`}>
                        {row.original.status}
                    </span>
                );
            },
        },
        {
            header: 'Notes',
            accessorKey: 'notes',
            cell: ({ row }) => (
                <div className="text-sm text-slate-600 dark:text-slate-400 truncate max-w-xs">
                    {row.original.notes || '-'}
                </div>
            ),
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex space-x-2">
                    <button
                        onClick={() => handleEdit(row.original)}
                        className="text-blue-600 hover:text-blue-900"
                        title="Edit"
                    >
                        <PencilIcon className="h-5 w-5" />
                    </button>
                    <button
                        onClick={() => handleDelete(row.original.id)}
                        className="text-red-600 hover:text-red-900"
                        title="Delete"
                    >
                        <TrashIcon className="h-5 w-5" />
                    </button>
                </div>
            ),
        },
    ];

    const handleEdit = (code) => {
        setEditingCode(code);
        setData({
            product_id: code.product_id,
            code: code.code,
            status: code.status,
            notes: code.notes || '',
        });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this product code?')) {
            setIsDeleting(true);
            router.delete(route('admin.product-codes.destroy', id), {
                onSuccess: () => {
                    toast.success('Product code deleted successfully');
                    setIsDeleting(false);
                },
                onError: (errors) => {
                    toast.error(errors.error || 'Failed to delete product code');
                    setIsDeleting(false);
                },
            });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (editingCode) {
            put(route('admin.product-codes.update', editingCode.id), {
                onSuccess: () => {
                    toast.success('Product code updated successfully');
                    setShowModal(false);
                    reset();
                },
                onError: () => toast.error('Failed to update product code'),
            });
        } else {
            post(route('admin.product-codes.store'), {
                onSuccess: () => {
                    toast.success('Product code created successfully');
                    setShowModal(false);
                    reset();
                },
                onError: () => toast.error('Failed to create product code'),
            });
        }
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setEditingCode(null);
        reset();
    };

    const handleBulkStatusChange = (newStatus) => {
        if (selectedIds.length === 0) {
            toast.error('No items selected');
            return;
        }

        post(route('admin.product-codes.bulk-update-status'), {
            ids: selectedIds,
            status: newStatus,
        }, {
            onSuccess: () => {
                toast.success(`${selectedIds.length} product codes updated`);
                clearSelection();
            },
            onError: () => toast.error('Failed to update product codes'),
        });
    };

    const handleBulkDelete = () => {
        if (selectedIds.length === 0) {
            toast.error('No items selected');
            return;
        }

        // Optimistic UI - immediately clear selection and show loading
        const countToDelete = selectedIds.length;
        setIsDeleting(true);

        // Use fetch instead of router for faster response
        fetch(route('admin.product-codes.bulk-destroy'), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({
                ids: selectedIds,
            }),
        })
            .then(response => response.json())
            .then(data => {
                setIsDeleting(false);
                
                if (data.message) {
                    toast.success(data.message);
                } else {
                    toast.success(`${countToDelete} product code(s) deleted successfully`);
                }
                
                clearSelection();
                // Reload data
                router.reload();
            })
            .catch(error => {
                setIsDeleting(false);
                console.error('Delete error:', error);
                toast.error('Failed to delete product codes');
            });
    };

    return (
        <AdminLayout auth={auth}>
            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center mb-6">
                        <div>
                            <h1 className="text-3xl font-bold text-slate-900 dark:text-slate-100">Product Codes</h1>
                            <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Manage product units and availability</p>
                        </div>
                        <button
                            onClick={() => {
                                setEditingCode(null);
                                reset();
                                setShowModal(true);
                            }}
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                        >
                            <PlusIcon className="h-5 w-5 mr-2" />
                            Add Code
                        </button>
                    </div>

                    {selectedIds.length > 0 && (
                        <BulkActionsToolbar
                            selectedIds={selectedIds}
                            selectedCount={selectedIds.length}
                            selectAllChecked={selectAllChecked}
                            totalItems={productCodes.data?.length || 0}
                            onSelectAll={toggleSelectAll}
                            onDeleteSelected={handleBulkDelete}
                            isLoading={isDeleting}
                            actions={[
                                {
                                    id: 'available',
                                    label: 'Mark as Available',
                                    handler: () => handleBulkStatusChange('available'),
                                },
                                {
                                    id: 'unavailable',
                                    label: 'Mark as Unavailable',
                                    handler: () => handleBulkStatusChange('unavailable'),
                                },
                                {
                                    id: 'maintenance',
                                    label: 'Mark as Maintenance',
                                    handler: () => handleBulkStatusChange('maintenance'),
                                },
                            ]}
                        />
                    )}

                    <DataTable columns={columns} data={productCodes.data || []} />

                    {productCodes.links && (
                        <div className="mt-4 flex justify-center">
                            {/* Add pagination component here if needed */}
                        </div>
                    )}
                </div>
            </div>

            <Modal isOpen={showModal} onClose={handleCloseModal} title={editingCode ? 'Edit Product Code' : 'Add Product Code'}>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Product <span className="text-red-500">*</span>
                        </label>
                        <select
                            value={data.product_id}
                            onChange={e => setData('product_id', e.target.value)}
                            disabled={editingCode}
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                            required
                        >
                            <option value="">Select Product</option>
                            {products?.map(product => (
                                <option key={product.id} value={product.id}>
                                    {product.name} ({product.code})
                                </option>
                            ))}
                        </select>
                        {errors.product_id && <p className="mt-1 text-sm text-red-600">{errors.product_id}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Code <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={data.code}
                            onChange={e => setData('code', e.target.value)}
                            placeholder="e.g., atv-1, villa-lumbing-4"
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                            required
                        />
                        {errors.code && <p className="mt-1 text-sm text-red-600">{errors.code}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Status
                        </label>
                        <select
                            value={data.status}
                            onChange={e => setData('status', e.target.value)}
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                        >
                            <option value="available">Available</option>
                            <option value="unavailable">Unavailable</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        {errors.status && <p className="mt-1 text-sm text-red-600">{errors.status}</p>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Notes
                        </label>
                        <textarea
                            value={data.notes}
                            onChange={e => setData('notes', e.target.value)}
                            rows={3}
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                        />
                        {errors.notes && <p className="mt-1 text-sm text-red-600">{errors.notes}</p>}
                    </div>

                    <div className="flex gap-3 justify-end pt-4">
                        <button
                            type="button"
                            onClick={handleCloseModal}
                            className="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 rounded-md hover:bg-slate-200 dark:hover:bg-slate-600"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : (editingCode ? 'Update' : 'Create')}
                        </button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
