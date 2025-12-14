import { useState } from 'react';
import { router, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import DataTable from '@/Components/Admin/DataTable';
import BulkActionsToolbar from '@/Components/Admin/BulkActionsToolbar';
import { PlusIcon, EyeIcon, TrashIcon } from '@heroicons/react/24/outline';
import { useBulkSelection } from '@/hooks/useBulkSelection';
import toast from 'react-hot-toast';

export default function BookingIndex({ auth, bookings, filters }) {
    const [isDeleting, setIsDeleting] = useState(false);
    const { selectedIds, selectAllChecked, toggleSelection, toggleSelectAll, clearSelection, isSelected } = useBulkSelection();
    const columns = [
        {
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={selectAllChecked}
                    onChange={() => toggleSelectAll(bookings.data || [])}
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
            header: 'Booking Code',
            accessorKey: 'booking_code',
            cell: ({ row }) => (
                <Link
                    href={route('admin.bookings.show', row.original.id)}
                    className="text-blue-600 hover:text-blue-900 font-medium"
                >
                    {row.original.booking_code}
                </Link>
            ),
        },
        {
            header: 'Customer',
            accessorKey: 'customer_name',
            cell: ({ row }) => (
                <div>
                    <div className="font-medium">{row.original.customer_name}</div>
                    <div className="text-sm text-slate-500 dark:text-slate-400">{row.original.customer_phone}</div>
                </div>
            ),
        },
        {
            header: 'Check In',
            accessorKey: 'checkin_date',
        },
        {
            header: 'Check Out',
            accessorKey: 'checkout_date',
        },
        {
            header: 'Total Amount',
            accessorKey: 'total_amount',
            cell: ({ row }) => 'Rp ' + Number(row.original.total_amount).toLocaleString('id-ID'),
        },
        {
            header: 'Status',
            accessorKey: 'status',
            cell: ({ row }) => {
                const statusColors = {
                    'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                    'confirmed': 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
                    'checked_in': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                    'checked_out': 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200',
                    'cancelled': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                };
                return (
                    <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusColors[row.original.status]}`}>
                        {row.original.status.replace('_', ' ')}
                    </span>
                );
            },
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: ({ row }) => (
                <div className="flex space-x-2">
                    <Link
                        href={route('admin.bookings.show', row.original.id)}
                        className="text-blue-600 hover:text-blue-900"
                        title="View Details"
                    >
                        <EyeIcon className="h-5 w-5" />
                    </Link>
                    {row.original.status === 'pending' && (
                        <button
                            onClick={() => handleDelete(row.original.id)}
                            className="text-red-600 hover:text-red-900"
                            title="Delete"
                        >
                            <TrashIcon className="h-5 w-5" />
                        </button>
                    )}
                </div>
            ),
        },
    ];

    const handleDelete = (id) => {
        if (confirm('Are you sure you want to delete this booking?')) {
            fetch(route('admin.bookings.destroy', id), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.error || 'Failed to delete booking');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    toast.success('Booking deleted successfully');
                    clearSelection();
                    router.reload();
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    toast.error(error.message || 'Failed to delete booking');
                });
        }
    };

    const handleDeleteSelected = () => {
        if (selectedIds.length === 0) {
            toast.error('No items selected');
            return;
        }

        const countToDelete = selectedIds.length;
        setIsDeleting(true);

        fetch(route('admin.bookings.bulk-delete'), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
            },
            body: JSON.stringify({ ids: selectedIds }),
        })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.error || 'Failed to delete bookings');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.message) {
                    toast.success(data.message);
                } else {
                    toast.success(`${countToDelete} booking(s) deleted successfully`);
                }
                clearSelection();
                setIsDeleting(false);
                router.reload();
            })
            .catch(error => {
                setIsDeleting(false);
                console.error('Delete error:', error);
                toast.error(error.message || 'Failed to delete bookings');
            });
    };

    return (
        <AdminLayout auth={auth} title="Bookings">
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Bookings</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            Manage customer bookings and reservations
                        </p>
                    </div>
                    <Link
                        href={route('admin.bookings.create')}
                        className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700"
                    >
                        <PlusIcon className="-ml-1 mr-2 h-5 w-5" />
                        New Booking
                    </Link>
                </div>
            </div>

            {/* Filters */}
            <div className="mb-4 bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Search</label>
                        <input
                            type="text"
                            defaultValue={filters?.search}
                            onChange={(e) => router.get(route('admin.bookings.index'), 
                                { ...filters, search: e.target.value }, 
                                { preserveState: true, replace: true }
                            )}
                            placeholder="Search by code, name, phone..."
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select
                            defaultValue={filters?.status || ''}
                            onChange={(e) => router.get(route('admin.bookings.index'), 
                                { ...filters, status: e.target.value }, 
                                { preserveState: true, replace: true }
                            )}
                            className="w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                        >
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            {/* Bulk Actions Toolbar */}
            <BulkActionsToolbar
                selectedIds={selectedIds}
                selectAllChecked={selectAllChecked}
                totalItems={bookings.data?.length || 0}
                onSelectAll={() => toggleSelectAll(bookings.data || [])}
                onDeleteSelected={handleDeleteSelected}
                isLoading={isDeleting}
            />

            <DataTable
                columns={columns}
                data={bookings.data}
                pagination={bookings}
                routeName="admin.bookings.index"
                filters={filters}
            />
        </AdminLayout>
    );
}
