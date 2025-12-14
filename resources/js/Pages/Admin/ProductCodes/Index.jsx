import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import BulkActionsToolbar from '@/Components/Admin/BulkActionsToolbar';
import Modal from '@/Components/Modal';
import { PlusIcon, PencilIcon, TrashIcon, SparklesIcon } from '@heroicons/react/24/outline';
import { useBulkSelection } from '@/hooks/useBulkSelection';
import toast from 'react-hot-toast';

export default function ProductCodeIndex({ auth, availabilities, products, categories, filters }) {
    const [showModal, setShowModal] = useState(false);
    const [editingUnit, setEditingUnit] = useState(null);
    const [isDeleting, setIsDeleting] = useState(false);
    const { selectedIds, selectAllChecked, toggleSelection, toggleSelectAll, clearSelection, isSelected } = useBulkSelection();

    const { data, setData, post, put, processing, errors, reset } = useForm({
        product_id: '',
        parent_unit: '',
        unit_name: '',
        unit_code: '',
        max_capacity: 1,
        description: '',
        status: 'available',
    });

    const columns = [
        {
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={selectAllChecked}
                    onChange={() => toggleSelectAll(availabilities.data || [])}
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
            header: 'Produk',
            accessorKey: 'product_name',
            cell: ({ row }) => (
                <div className="flex flex-col">
                    <span className="font-medium text-slate-900 dark:text-slate-100">
                        {row.original.product_name}
                    </span>
                    <span className="text-xs text-slate-500 dark:text-slate-400">
                        ({row.original.product_code})
                    </span>
                </div>
            ),
        },
        {
            header: 'Bangunan/Area',
            accessorKey: 'parent_unit',
        },
        {
            header: 'Unit/Ruangan',
            accessorKey: 'unit_name',
            cell: ({ row }) => (
                <div className="flex flex-col">
                    <span className="font-medium text-slate-900 dark:text-slate-100">
                        {row.original.unit_name}
                    </span>
                    <span className="text-xs text-slate-500 dark:text-slate-400">
                        Code: {row.original.unit_code}
                    </span>
                </div>
            ),
        },
        {
            header: 'Kapasitas',
            accessorKey: 'max_capacity',
            cell: ({ row }) => (
                <span className="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-semibold">
                    {row.original.max_capacity}
                </span>
            ),
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
                const statusLabel = {
                    'available': 'Tersedia',
                    'unavailable': 'Tidak Tersedia',
                    'maintenance': 'Maintenance',
                };
                return (
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[row.original.status] || ''}`}>
                        {statusLabel[row.original.status] || row.original.status}
                    </span>
                );
            },
        },
        {
            header: 'Kategori',
            accessorKey: 'category_type',
            cell: ({ row }) => {
                const categoryLabels = {
                    'villa': '🏠 Villa',
                    'ticket': '🎫 Tiket',
                    'parking': '🅿️ Parking',
                };
                return <span>{categoryLabels[row.original.category_type] || row.original.category_type}</span>;
            },
        },
        {
            header: 'Aksi',
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex space-x-2">
                    <button
                        onClick={() => handleEdit(row.original)}
                        className="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                        title="Edit"
                    >
                        <PencilIcon className="h-5 w-5" />
                    </button>
                    <button
                        onClick={() => handleDelete(row.original.id)}
                        className="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                        title="Hapus"
                    >
                        <TrashIcon className="h-5 w-5" />
                    </button>
                </div>
            ),
        },
    ];

    const handleEdit = (unit) => {
        setEditingUnit(unit);
        setData({
            product_id: unit.product_id,
            parent_unit: unit.parent_unit,
            unit_name: unit.unit_name,
            unit_code: unit.unit_code,
            max_capacity: unit.max_capacity,
            description: unit.description || '',
            status: unit.status,
        });
        setShowModal(true);
    };

    const handleDelete = (id) => {
        if (confirm('Yakin hapus unit ini?')) {
            setIsDeleting(true);
            fetch(route('product-codes.destroy', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Gagal hapus unit');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    toast.success('Unit berhasil dihapus');
                    setIsDeleting(false);
                    router.reload();
                })
                .catch(error => {
                    setIsDeleting(false);
                    toast.error(error.message || 'Gagal hapus unit');
                });
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        if (editingUnit) {
            put(route('product-codes.update', editingUnit.id), {
                onSuccess: () => {
                    toast.success('Unit berhasil diupdate');
                    setShowModal(false);
                    reset();
                    router.reload();
                },
                onError: (errors) => {
                    Object.values(errors).forEach(error => {
                        toast.error(error);
                    });
                },
            });
        } else {
            post(route('product-codes.store'), {
                onSuccess: () => {
                    toast.success('Unit berhasil ditambahkan');
                    setShowModal(false);
                    reset();
                    router.reload();
                },
                onError: (errors) => {
                    Object.values(errors).forEach(error => {
                        toast.error(error);
                    });
                },
            });
        }
    };

    const handleAddNew = () => {
        setEditingUnit(null);
        setData({
            product_id: '',
            parent_unit: '',
            unit_name: '',
            unit_code: '',
            max_capacity: 1,
            description: '',
            status: 'available',
        });
        setShowModal(true);
    };

    const handleBulkStatusUpdate = async (newStatus) => {
        if (selectedIds.length === 0) {
            toast.error('Pilih minimal 1 unit');
            return;
        }

        try {
            const response = await fetch(route('product-codes.bulk-update-status'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({
                    ids: selectedIds,
                    status: newStatus,
                }),
            });

            if (!response.ok) {
                throw new Error('Gagal update status');
            }

            toast.success(`${selectedIds.length} unit berhasil diupdate`);
            clearSelection();
            router.reload();
        } catch (error) {
            toast.error(error.message);
        }
    };

    const handleBulkDelete = async () => {
        if (selectedIds.length === 0) {
            toast.error('Pilih minimal 1 unit');
            return;
        }

        if (!confirm(`Hapus ${selectedIds.length} unit?`)) return;

        try {
            const response = await fetch(route('product-codes.bulk-destroy'), {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({ ids: selectedIds }),
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.error || 'Gagal hapus units');
            }

            toast.success(`${selectedIds.length} unit berhasil dihapus`);
            clearSelection();
            router.reload();
        } catch (error) {
            toast.error(error.message);
        }
    };

    return (
        <AdminLayout user={auth.user}>
            <div className="space-y-6 p-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-3">
                        <SparklesIcon className="h-8 w-8 text-blue-600 dark:text-blue-400" />
                        <div>
                            <h1 className="text-3xl font-bold text-slate-900 dark:text-slate-100">
                                Unit Produk
                            </h1>
                            <p className="text-slate-600 dark:text-slate-400">
                                Kelola unit/kamar/area untuk setiap produk
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={handleAddNew}
                        className="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                    >
                        <PlusIcon className="h-5 w-5" />
                        <span>Tambah Unit</span>
                    </button>
                </div>

                {/* Filters */}
                <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-4 space-y-4">
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Produk
                            </label>
                            <select
                                defaultValue={filters.product_id || ''}
                                onChange={(e) => router.get(route('product-codes.index'), { ...filters, product_id: e.target.value })}
                                className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                            >
                                <option value="">Semua Produk</option>
                                {products.map((product) => (
                                    <option key={product.id} value={product.id}>
                                        {product.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Kategori
                            </label>
                            <select
                                defaultValue={filters.category || ''}
                                onChange={(e) => router.get(route('product-codes.index'), { ...filters, category: e.target.value })}
                                className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                            >
                                <option value="">Semua Kategori</option>
                                {Object.entries(categories).map(([key, label]) => (
                                    <option key={key} value={key}>
                                        {label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Status
                            </label>
                            <select
                                defaultValue={filters.status || ''}
                                onChange={(e) => router.get(route('product-codes.index'), { ...filters, status: e.target.value })}
                                className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                            >
                                <option value="">Semua Status</option>
                                <option value="available">Tersedia</option>
                                <option value="unavailable">Tidak Tersedia</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                                Cari
                            </label>
                            <input
                                type="text"
                                defaultValue={filters.search || ''}
                                placeholder="Cari unit..."
                                onChange={(e) => {
                                    const timer = setTimeout(() => {
                                        router.get(route('product-codes.index'), { ...filters, search: e.target.value });
                                    }, 300);
                                    return () => clearTimeout(timer);
                                }}
                                className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                            />
                        </div>
                    </div>
                </div>

                {/* Bulk Actions Toolbar */}
                {selectedIds.length > 0 && (
                    <BulkActionsToolbar
                        selectedCount={selectedIds.length}
                        onStatusChange={handleBulkStatusUpdate}
                        onDelete={handleBulkDelete}
                        statuses={[
                            { value: 'available', label: 'Tersedia' },
                            { value: 'unavailable', label: 'Tidak Tersedia' },
                            { value: 'maintenance', label: 'Maintenance' },
                        ]}
                    />
                )}

                {/* Data Table */}
                <div className="bg-white dark:bg-slate-800 rounded-lg shadow overflow-hidden">
                    <DataTable columns={columns} data={availabilities.data || []} />

                    {/* Pagination */}
                    {availabilities.links && (
                        <div className="p-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <div className="text-sm text-slate-600 dark:text-slate-400">
                                Menampilkan {availabilities.from} ke {availabilities.to} dari {availabilities.total} unit
                            </div>
                            <div className="space-x-1">
                                {availabilities.links.map((link, index) => (
                                    <a
                                        key={index}
                                        href={link.url}
                                        className={`px-3 py-1 rounded text-sm ${
                                            link.active
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-slate-100 hover:bg-slate-300 dark:hover:bg-slate-600'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Modal Add/Edit */}
            <Modal show={showModal} onClose={() => setShowModal(false)} maxWidth="md">
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-4">
                        {editingUnit ? 'Edit Unit' : 'Tambah Unit Baru'}
                    </h2>

                    {/* Product Selection */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Produk <span className="text-red-500">*</span>
                        </label>
                        <select
                            value={data.product_id}
                            onChange={(e) => setData('product_id', e.target.value)}
                            disabled={editingUnit !== null}
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg disabled:opacity-50"
                        >
                            <option value="">Pilih Produk</option>
                            {products.map((product) => (
                                <option key={product.id} value={product.id}>
                                    {product.name}
                                </option>
                            ))}
                        </select>
                        {errors.product_id && (
                            <p className="text-red-500 text-sm mt-1">{errors.product_id}</p>
                        )}
                    </div>

                    {/* Parent Unit */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Bangunan/Area <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={data.parent_unit}
                            onChange={(e) => setData('parent_unit', e.target.value)}
                            placeholder="e.g., Villa Bungalow, Kolam Renang A"
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                        />
                        {errors.parent_unit && (
                            <p className="text-red-500 text-sm mt-1">{errors.parent_unit}</p>
                        )}
                    </div>

                    {/* Unit Name */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Unit/Ruangan <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={data.unit_name}
                            onChange={(e) => setData('unit_name', e.target.value)}
                            placeholder="e.g., Kamar A, Area Renang Dewasa"
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                        />
                        {errors.unit_name && (
                            <p className="text-red-500 text-sm mt-1">{errors.unit_name}</p>
                        )}
                    </div>

                    {/* Unit Code */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Kode Unit <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            value={data.unit_code}
                            onChange={(e) => setData('unit_code', e.target.value)}
                            placeholder="e.g., VILLA-BUNG-A, KLM-RENANG-1"
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                        />
                        {errors.unit_code && (
                            <p className="text-red-500 text-sm mt-1">{errors.unit_code}</p>
                        )}
                    </div>

                    {/* Max Capacity */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Kapasitas Maksimal <span className="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            value={data.max_capacity}
                            onChange={(e) => setData('max_capacity', parseInt(e.target.value))}
                            min="1"
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                        />
                        {errors.max_capacity && (
                            <p className="text-red-500 text-sm mt-1">{errors.max_capacity}</p>
                        )}
                    </div>

                    {/* Description */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Deskripsi
                        </label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Catatan tambahan tentang unit ini"
                            rows="3"
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                        />
                        {errors.description && (
                            <p className="text-red-500 text-sm mt-1">{errors.description}</p>
                        )}
                    </div>

                    {/* Status */}
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Status
                        </label>
                        <select
                            value={data.status}
                            onChange={(e) => setData('status', e.target.value)}
                            className="w-full px-3 py-2 border border-slate-300 dark:border-slate-600 dark:bg-slate-700 rounded-lg"
                        >
                            <option value="available">Tersedia</option>
                            <option value="unavailable">Tidak Tersedia</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        {errors.status && (
                            <p className="text-red-500 text-sm mt-1">{errors.status}</p>
                        )}
                    </div>

                    {/* Buttons */}
                    <div className="flex space-x-3 pt-4">
                        <button
                            type="button"
                            onClick={() => setShowModal(false)}
                            className="flex-1 px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600 text-white rounded-lg disabled:opacity-50"
                        >
                            {processing ? 'Menyimpan...' : editingUnit ? 'Update' : 'Tambah'}
                        </button>
                    </div>
                </form>
            </Modal>
        </AdminLayout>
    );
}
