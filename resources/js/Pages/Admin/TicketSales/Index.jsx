    import { useState, useEffect } from 'react';
    import { router, Link, usePage } from '@inertiajs/react';
    import AdminLayout from '@/Layouts/AdminLayout';
    import DataTable from '@/Components/Admin/DataTable';
    import BulkActionsToolbar from '@/Components/Admin/BulkActionsToolbar';
    import { PlusIcon, EyeIcon, PrinterIcon } from '@heroicons/react/24/outline';
    import { useBulkSelection } from '@/hooks/useBulkSelection';
    import { format } from 'date-fns';
    import toast from 'react-hot-toast';

    export default function TicketSaleIndex({ auth, ticketSales, filters }) {
        const { flash } = usePage().props;
        const [hasShownFlash, setHasShownFlash] = useState(false);
        const [isDeleting, setIsDeleting] = useState(false);
        const { selectedIds, selectAllChecked, toggleSelection, toggleSelectAll, clearSelection, isSelected } = useBulkSelection();

        // Show flash messages
        useEffect(() => {
            if (!hasShownFlash && flash) {
                if (flash.success) {
                    toast.success(flash.success);
                    setHasShownFlash(true);
                }
                if (flash.error) {
                    toast.error(flash.error);
                    setHasShownFlash(true);
                }
            }
        }, [flash, hasShownFlash]);
        
        const columns = [
            {
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={selectAllChecked}
                        onChange={() => toggleSelectAll(ticketSales.data || [])}
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
                header: 'Invoice No',
                accessorKey: 'invoice_no',
                cell: ({ row }) => (
                    <Link
                        href={`/admin/ticket-sales/${row.original.id}`}
                        className="text-blue-600 hover:text-blue-900 font-medium"
                    >
                        {row.original.invoice_no}
                    </Link>
                ),
            },
            {
                header: 'Sale Date',
                accessorKey: 'sale_date',
                cell: ({ row }) => format(new Date(row.original.sale_date), 'dd MMM yyyy HH:mm'),
            },
            {
                header: 'Cashier',
                accessorKey: 'cashier_name',
            },
            {
                header: 'Total Qty',
                accessorKey: 'total_qty',
            },
            {
                header: 'Gross Amount',
                accessorKey: 'gross_amount',
                cell: ({ row }) => `Rp ${Number(row.original.gross_amount).toLocaleString('id-ID')}`,
            },
            {
                header: 'Discount',
                accessorKey: 'discount_amount',
                cell: ({ row }) => `Rp ${Number(row.original.discount_amount).toLocaleString('id-ID')}`,
            },
            {
                header: 'Net Amount',
                accessorKey: 'net_amount',
                cell: ({ row }) => (
                    <span className="font-semibold text-slate-900 dark:text-slate-100">
                        Rp {Number(row.original.net_amount).toLocaleString('id-ID')}
                    </span>
                ),
            },
            {
                header: 'Status',
                accessorKey: 'status',
                cell: ({ row }) => {
                    const statusColors = {
                        'open': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
                        'paid': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
                        'void': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200',
                    };
                    return (
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            statusColors[row.original.status] || 'bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200'
                        }`}>
                            {row.original.status}
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
                            href={`/admin/ticket-sales/${row.original.id}`}
                            className="text-blue-600 hover:text-blue-900"
                        >
                            <EyeIcon className="h-5 w-5" />
                        </Link>
                        <button
                            onClick={() => handlePrint(row.original.id)}
                            className="text-slate-600 hover:text-slate-900 dark:text-slate-100"
                        >
                            <PrinterIcon className="h-5 w-5" />
                        </button>
                    </div>
                ),
            },
        ];

        const handlePrint = (id) => {
            window.open(`/admin/ticket-sales/${id}/print`, '_blank');
        };

        const handleDeleteSelected = () => {
            if (selectedIds.length === 0) {
                toast.error('No items selected');
                return;
            }

            setIsDeleting(true);
            router.delete(route('admin.ticket-sales.bulk-delete'), 
                { ids: selectedIds },
                {
                    onSuccess: () => {
                        toast.success(`${selectedIds.length} ticket sale(s) deleted successfully`);
                        clearSelection();
                        setIsDeleting(false);
                    },
                    onError: (errors) => {
                        toast.error(errors.error || 'Failed to delete ticket sales');
                        setIsDeleting(false);
                    }
                }
            );
        };

        return (
            <AdminLayout auth={auth} title="Ticket Sales">
                <div className="mb-6">
                    <div className="flex justify-between items-center">
                        <div>
                            <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Ticket Sales</h1>
                            <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">
                                Manage ticket transactions
                            </p>
                        </div>
                        <Link
                            href="/admin/ticket-sales/create"
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <PlusIcon className="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
                            New Sale
                        </Link>
                    </div>
                </div>

                {/* Filter bar */}
                <div className="mb-4 bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div>
                            <label htmlFor="ts-filter-status" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                            <select id="ts-filter-status" name="status" className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600">
                                <option value="">All</option>
                                <option value="open">Open</option>
                                <option value="paid">Paid</option>
                                <option value="void">Void</option>
                            </select>
                        </div>
                        <div>
                            <label htmlFor="ts-filter-from" className="block text-sm font-medium text-slate-700 dark:text-slate-300">From Date</label>
                            <input
                                id="ts-filter-from"
                                name="from_date"
                                type="date"
                                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                            />
                        </div>
                        <div>
                            <label htmlFor="ts-filter-to" className="block text-sm font-medium text-slate-700 dark:text-slate-300">To Date</label>
                            <input
                                id="ts-filter-to"
                                name="to_date"
                                type="date"
                                className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                            />
                        </div>
                        <div>
                            <label htmlFor="ts-filter-cashier" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Cashier</label>
                            <select id="ts-filter-cashier" name="cashier" className="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600">
                                <option value="">All Cashiers</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Bulk Actions Toolbar */}
                <BulkActionsToolbar
                    selectedIds={selectedIds}
                    selectAllChecked={selectAllChecked}
                    totalItems={ticketSales.data?.length || 0}
                    onSelectAll={() => toggleSelectAll(ticketSales.data || [])}
                    onDeleteSelected={handleDeleteSelected}
                    isLoading={isDeleting}
                />

                <DataTable
                    columns={columns}
                    data={ticketSales.data}
                    pagination={ticketSales}
                    routeName="admin.ticket-sales.index"
                    filters={filters}
                />
            </AdminLayout>
        );
    }
