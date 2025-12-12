import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import BulkActionsToolbar from '@/Components/Admin/BulkActionsToolbar';
import Modal from '@/Components/Modal';
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/react/24/outline';
import { useBulkSelection } from '@/hooks/useBulkSelection';
import toast from 'react-hot-toast';

export default function ProductIndex({ auth, products, categories, filters }) {
    const [showModal, setShowModal] = useState(false);
    const [editingProduct, setEditingProduct] = useState(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const { selectedIds, selectAllChecked, toggleSelection, toggleSelectAll, clearSelection, isSelected } = useBulkSelection();

    const { data, setData, post, put, processing, errors, reset } = useForm({
        category_id: '',
        code: '',
        name: '',
        base_price: '',
        is_active: true,
    });

    const columns = [
        {
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={selectAllChecked}
                    onChange={() => toggleSelectAll(products.data || [])}
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
            header: 'Name',
            accessorKey: 'name',
        },
        {
            header: 'Category',
            accessorKey: 'category_name',
        },
        {
            header: 'Base Price',
            accessorKey: 'base_price',
            cell: ({ row }) => 'Rp ' + Number(row.original.base_price).toLocaleString('id-ID'),
        },
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

    const handleEdit = (product) => {
        setEditingProduct(product);
        setData({
            category_id: product.category_id || '',
            code: product.code,
            name: product.name,
            base_price: product.base_price,
            is_active: product.is_active,
        });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this product?')) {
            router.delete(route('admin.products.destroy', id), {
                onSuccess: () => {
                    toast.success('Product deleted successfully');
                    clearSelection();
                },
                onError: (errors) => toast.error(errors.error || 'Failed to delete product'),
            });
        }
    };

    const handleDeleteSelected = () => {
        if (selectedIds.length === 0) {
            toast.error('No items selected');
            return;
        }

        setIsDeleting(true);
        router.delete(route('admin.products.bulk-delete'), 
            { ids: selectedIds },
            {
                onSuccess: () => {
                    toast.success(`${selectedIds.length} product(s) deleted successfully`);
                    clearSelection();
                    setIsDeleting(false);
                },
                onError: (errors) => {
                    toast.error(errors.error || 'Failed to delete products');
                    setIsDeleting(false);
                }
            }
        );
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const options = {
            onSuccess: () => {
                toast.success(editingProduct ? 'Product updated successfully' : 'Product created successfully');
                closeModal();
            },
            onError: () => toast.error('Failed to save product'),
        };

        if (editingProduct) {
            put(route('admin.products.update', editingProduct.id), options);
        } else {
            post(route('admin.products.store'), options);
        }
    };

    const closeModal = () => {
        setShowModal(false);
        setEditingProduct(null);
        reset();
    };

    return (
        <AdminLayout auth={auth} title="Products">
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Products</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Manage your product catalog
                        </p>
                    </div>
                    <button
                        onClick={() => {
                            setEditingProduct(null);
                            reset();
                            setShowModal(true);
                        }}
                        className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <PlusIcon className="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                        Add Product
                    </button>
                </div>
            </div>

            {/* Filters */}
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-4 mb-6">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label htmlFor="filter-search" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Search</label>
                        <input
                            id="filter-search"
                            name="search"
                            type="text"
                            defaultValue={filters?.search}
                            onChange={(e) => router.get(route('admin.products.index'), 
                                { ...filters, search: e.target.value }, 
                                { preserveState: true, replace: true }
                            )}
                            placeholder="Search by name or code..."
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                        />
                    </div>
                    <div>
                        <label htmlFor="filter-category" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
                        <select
                            id="filter-category"
                            name="category_id"
                            defaultValue={filters?.category_id || ''}
                            onChange={(e) => router.get(route('admin.products.index'), 
                                { ...filters, category_id: e.target.value }, 
                                { preserveState: true, replace: true }
                            )}
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                        >
                            <option value="">All Categories</option>
                            {categories.map(cat => (
                                <option key={cat.id} value={cat.id}>{cat.name}</option>
                            ))}
                        </select>
                    </div>
                </div>
            </div>

            {/* Bulk Actions Toolbar */}
            <BulkActionsToolbar
                selectedIds={selectedIds}
                selectAllChecked={selectAllChecked}
                totalItems={products.data?.length || 0}
                onSelectAll={() => toggleSelectAll(products.data || [])}
                onDeleteSelected={handleDeleteSelected}
                isLoading={isDeleting}
            />

            <DataTable
                columns={columns}
                data={products.data}
                pagination={products}
                routeName="admin.products.index"
                filters={filters}
            />

            {/* Create/Edit Modal */}
            <Modal show={showModal} onClose={closeModal} maxWidth="2xl">
                <form onSubmit={handleSubmit} className="p-6">
                    <h2 className="text-xl font-semibold mb-4 dark:text-slate-100">
                        {editingProduct ? 'Edit Product' : 'Add New Product'}
                    </h2>

                    <div className="space-y-4">
                        <div>
                            <label htmlFor="product-category" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Category <span className="text-red-500">*</span>
                                </label>
                                <select
                                    id="product-category"
                                    name="category_id"
                                    value={data.category_id}
                                    onChange={e => setData('category_id', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                                    required
                                >
                                <option value="">Select Category</option>
                                {categories.map(cat => (
                                    <option key={cat.id} value={cat.id}>{cat.name}</option>
                                ))}
                            </select>
                            {errors.category_id && <p className="mt-1 text-sm text-red-600">{errors.category_id}</p>}
                        </div>

                        <div>
                            <label htmlFor="product-code" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Product Code <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="product-code"
                                    name="code"
                                    type="text"
                                    value={data.code}
                                    onChange={e => setData('code', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                                    placeholder="e.g. VILLA-A"
                                    maxLength={30}
                                    required
                                />
                            {errors.code && <p className="mt-1 text-sm text-red-600">{errors.code}</p>}
                        </div>

                        <div>
                            <label htmlFor="product-name" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Product Name <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="product-name"
                                    name="name"
                                    type="text"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                                    placeholder="e.g. Villa Premium A"
                                    maxLength={100}
                                    required
                                />
                            {errors.name && <p className="mt-1 text-sm text-red-600">{errors.name}</p>}
                        </div>

                        <div>
                            <label htmlFor="product-base-price" className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                    Base Price (Rp) <span className="text-red-500">*</span>
                                </label>
                                <input
                                    id="product-base-price"
                                    name="base_price"
                                    type="number"
                                    value={data.base_price}
                                    onChange={e => setData('base_price', e.target.value)}
                                    className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                                    placeholder="e.g. 1500000"
                                    min="0"
                                    step="1000"
                                    required
                                />
                            {errors.base_price && <p className="mt-1 text-sm text-red-600">{errors.base_price}</p>}
                        </div>

                        <div className="flex items-center">
                            <input
                                id="product-is-active"
                                name="is_active"
                                type="checkbox"
                                checked={data.is_active}
                                onChange={e => setData('is_active', e.target.checked)}
                                className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-slate-300 rounded dark:bg-slate-700 dark:border-slate-600"
                            />
                            <label htmlFor="product-is-active" className="ml-2 block text-sm text-slate-900 dark:text-slate-100">
                                Active
                            </label>
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            onClick={closeModal}
                            className="px-4 py-2 border border-slate-300 rounded-md text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 dark:border-slate-600"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                        >
                            {processing ? 'Saving...' : (editingProduct ? 'Update' : 'Create')}
                        </button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
