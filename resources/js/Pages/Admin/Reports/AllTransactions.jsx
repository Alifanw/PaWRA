import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { useState, useMemo } from 'react';
import ReportDateExport from './ReportDateExport';
import BulkActionsToolbar from '@/Components/Admin/BulkActionsToolbar';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/react/24/outline';
import { useBulkSelection } from '@/hooks/useBulkSelection';
import toast from 'react-hot-toast';

export default function AllTransactionsReport({ auth, transactions, summary, filters }) {
    const [filterType, setFilterType] = useState('all'); // all, with-discount, without-discount
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [isDeleting, setIsDeleting] = useState(false);
    const { selectedIds, selectAllChecked, toggleSelection, toggleSelectAll, clearSelection, isSelected } = useBulkSelection();

    const filteredTransactions = useMemo(() => {
        const filtered = filterType === 'all' 
            ? transactions 
            : filterType === 'with-discount'
            ? transactions.filter(t => Number(t.discount_amount || 0) > 0)
            : transactions.filter(t => Number(t.discount_amount || 0) === 0);
        return filtered;
    }, [filterType, transactions]);

    // Calculate pagination
    const totalItems = filteredTransactions.length;
    const totalPages = Math.ceil(totalItems / perPage);
    const startIndex = (currentPage - 1) * perPage;
    const endIndex = startIndex + perPage;
    const paginatedTransactions = filteredTransactions.slice(startIndex, endIndex);

    // Reset to page 1 when filter changes
    const handleFilterChange = (newFilter) => {
        setFilterType(newFilter);
        setCurrentPage(1);
    };

    const handlePerPageChange = (newPerPage) => {
        setPerPage(newPerPage);
        setCurrentPage(1);
    };

    const handlePrevPage = () => {
        if (currentPage > 1) {
            setCurrentPage(currentPage - 1);
        }
    };

    const handleNextPage = () => {
        if (currentPage < totalPages) {
            setCurrentPage(currentPage + 1);
        }
    };

    const handlePageClick = (page) => {
        setCurrentPage(page);
    };

    const getTransactionType = (tx) => {
        if (tx.type === 'booking') return 'Booking';
        if (tx.type === 'ticket_sale') return 'Ticket Sale';
        if (tx.type === 'parking_transaction') return 'Parking TX';
        return tx.type;
    };

    const getStatusBadgeColor = (status) => {
        const colors = {
            'completed': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'confirmed': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'checked_in': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
            'checked_out': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
        };
        return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
    };

    const handleExportAll = () => {
        router.get(route('admin.reports.export-all-xlsx'), filters);
    };

    const handleDeleteSelected = () => {
        if (selectedIds.length === 0) {
            toast.error('No items selected');
            return;
        }

        setIsDeleting(true);
        router.delete(route('admin.reports.bulk-delete'), 
            { ids: selectedIds },
            {
                onSuccess: () => {
                    toast.success(`${selectedIds.length} transaction(s) deleted successfully`);
                    clearSelection();
                    setIsDeleting(false);
                },
                onError: (errors) => {
                    toast.error(errors.error || 'Failed to delete transactions');
                    setIsDeleting(false);
                }
            }
        );
    };

    return (
        <AdminLayout auth={auth} title="All Transactions Report">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold dark:text-slate-100">All Transactions Report</h1>
                <p className="text-sm text-slate-600 dark:text-slate-400">Complete transaction data with discount differentiation</p>
            </div>

            {/* Filters and Export */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                    <ReportDateExport
                        filters={filters}
                        onFilterChange={(newFilters) => router.get(route('admin.reports.all-transactions'), newFilters, {preserveState:true})}
                    />
                </div>

                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow flex items-end gap-2">
                    <button
                        onClick={handleExportAll}
                        className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 transition"
                    >
                        📊 Export All to Excel
                    </button>
                </div>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                    <div className="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Transactions</div>
                    <div className="text-2xl font-bold dark:text-slate-100">{summary.total_transactions}</div>
                </div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                    <div className="text-slate-500 dark:text-slate-400 text-sm font-medium">With Discount</div>
                    <div className="text-2xl font-bold text-orange-600 dark:text-orange-400">{summary.with_discount_count}</div>
                </div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                    <div className="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Discount</div>
                    <div className="text-xl font-bold text-orange-600 dark:text-orange-400">Rp {Number(summary.total_discount).toLocaleString('id-ID')}</div>
                </div>
                <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow">
                    <div className="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Revenue</div>
                    <div className="text-2xl font-bold text-green-600 dark:text-green-400">Rp {Number(summary.total_revenue).toLocaleString('id-ID')}</div>
                </div>
            </div>

            {/* Filter Tabs */}
            <div className="bg-white dark:bg-slate-800 p-4 rounded-lg shadow mb-6">
                <div className="flex gap-2 flex-wrap">
                    <button
                        onClick={() => handleFilterChange('all')}
                        className={`px-4 py-2 rounded-lg transition ${
                            filterType === 'all'
                                ? 'bg-blue-600 text-white dark:bg-blue-700'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 hover:bg-slate-200'
                        }`}
                    >
                        All Transactions ({transactions.length})
                    </button>
                    <button
                        onClick={() => handleFilterChange('with-discount')}
                        className={`px-4 py-2 rounded-lg transition ${
                            filterType === 'with-discount'
                                ? 'bg-orange-600 text-white dark:bg-orange-700'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 hover:bg-slate-200'
                        }`}
                    >
                        With Discount ({transactions.filter(t => Number(t.discount_amount || 0) > 0).length})
                    </button>
                    <button
                        onClick={() => handleFilterChange('without-discount')}
                        className={`px-4 py-2 rounded-lg transition ${
                            filterType === 'without-discount'
                                ? 'bg-green-600 text-white dark:bg-green-700'
                                : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 hover:bg-slate-200'
                        }`}
                    >
                        Without Discount ({transactions.filter(t => Number(t.discount_amount || 0) === 0).length})
                    </button>
                </div>
            </div>

            {/* Transactions Table */}
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead className="bg-slate-50 dark:bg-slate-700">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">
                                <input
                                    type="checkbox"
                                    checked={selectAllChecked}
                                    onChange={() => toggleSelectAll(paginatedTransactions || [])}
                                    className="rounded dark:bg-slate-700 dark:border-slate-600"
                                />
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Type</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Code/Invoice</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Date</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Name/Customer</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Qty/Nights</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Gross Amount</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Discount</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Net Amount</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        {paginatedTransactions.length > 0 ? (
                            paginatedTransactions.map((tx, idx) => (
                                <tr key={`${tx.type}-${tx.id}`} className="hover:bg-slate-50 dark:hover:bg-slate-700 transition">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            checked={isSelected(tx.id)}
                                            onChange={() => toggleSelection(tx.id)}
                                            className="rounded dark:bg-slate-700 dark:border-slate-600"
                                        />
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <span className="px-2 py-1 bg-slate-100 dark:bg-slate-700 rounded text-slate-700 dark:text-slate-300">
                                            {getTransactionType(tx)}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-semibold dark:text-slate-100">{tx.code_or_invoice}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm dark:text-slate-300">{tx.transaction_date}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm dark:text-slate-300">{tx.name_customer}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm dark:text-slate-300">{tx.qty_nights}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-right dark:text-slate-300">
                                        Rp {Number(tx.gross_amount).toLocaleString('id-ID')}
                                    </td>
                                    <td className={`px-6 py-4 whitespace-nowrap text-sm text-right font-semibold ${Number(tx.discount_amount || 0) > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-slate-500 dark:text-slate-400'}`}>
                                        {Number(tx.discount_amount || 0) > 0 
                                            ? `Rp ${Number(tx.discount_amount).toLocaleString('id-ID')}` 
                                            : '-'
                                        }
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-bold dark:text-slate-100">
                                        Rp {Number(tx.net_amount).toLocaleString('id-ID')}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm">
                                        <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusBadgeColor(tx.status)}`}>
                                            {tx.status}
                                        </span>
                                    </td>
                                </tr>
                            ))
                        ) : (
                            <tr>
                                <td colSpan="10" className="px-6 py-4 text-center text-slate-500 dark:text-slate-400">
                                    No transactions found for the selected period and filter.
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {/* Bulk Actions Toolbar */}
            {filteredTransactions.length > 0 && (
                <BulkActionsToolbar
                    selectedIds={selectedIds}
                    selectAllChecked={selectAllChecked}
                    totalItems={paginatedTransactions?.length || 0}
                    onSelectAll={() => toggleSelectAll(paginatedTransactions || [])}
                    onDeleteSelected={handleDeleteSelected}
                    isLoading={isDeleting}
                />
            )}

            {/* Pagination and Summary */}
            {filteredTransactions.length > 0 && (
                <>
                    {/* Pagination Controls */}
                    <div className="mt-4 bg-white dark:bg-slate-800 p-4 rounded-lg shadow flex items-center justify-between flex-wrap gap-4">
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-slate-600 dark:text-slate-400">Show per page:</span>
                            <select
                                value={perPage}
                                onChange={(e) => handlePerPageChange(Number(e.target.value))}
                                className="px-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100"
                            >
                                <option value={10}>10</option>
                                <option value={25}>25</option>
                                <option value={50}>50</option>
                                <option value={100}>100</option>
                                <option value={250}>250</option>
                                <option value={500}>500</option>
                            </select>
                        </div>

                        <div className="flex items-center gap-2 text-sm dark:text-slate-300">
                            Showing {startIndex + 1} to {Math.min(endIndex, totalItems)} of {totalItems} records
                        </div>

                        <div className="flex items-center gap-2">
                            <button
                                onClick={handlePrevPage}
                                disabled={currentPage === 1}
                                className={`p-2 rounded-lg border transition ${
                                    currentPage === 1
                                        ? 'border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500 cursor-not-allowed'
                                        : 'border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'
                                }`}
                            >
                                <ChevronLeftIcon className="w-5 h-5" />
                            </button>

                            <div className="flex gap-1">
                                {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                                    let pageNum;
                                    if (totalPages <= 5) {
                                        pageNum = i + 1;
                                    } else if (currentPage <= 3) {
                                        pageNum = i + 1;
                                    } else if (currentPage >= totalPages - 2) {
                                        pageNum = totalPages - 4 + i;
                                    } else {
                                        pageNum = currentPage - 2 + i;
                                    }
                                    return (
                                        <button
                                            key={pageNum}
                                            onClick={() => handlePageClick(pageNum)}
                                            className={`px-3 py-2 rounded-lg border transition ${
                                                currentPage === pageNum
                                                    ? 'bg-blue-600 text-white border-blue-600 dark:bg-blue-700 dark:border-blue-700'
                                                    : 'border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'
                                            }`}
                                        >
                                            {pageNum}
                                        </button>
                                    );
                                })}
                            </div>

                            <button
                                onClick={handleNextPage}
                                disabled={currentPage === totalPages}
                                className={`p-2 rounded-lg border transition ${
                                    currentPage === totalPages
                                        ? 'border-slate-200 dark:border-slate-600 text-slate-400 dark:text-slate-500 cursor-not-allowed'
                                        : 'border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700'
                                }`}
                            >
                                <ChevronRightIcon className="w-5 h-5" />
                            </button>

                            <span className="text-sm text-slate-600 dark:text-slate-400 ml-2">
                                Page {currentPage} of {totalPages}
                            </span>
                        </div>
                    </div>

                    {/* Summary Footer */}
                    <div className="mt-4 bg-slate-50 dark:bg-slate-700 p-4 rounded-lg border border-slate-200 dark:border-slate-600">
                        <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <div className="text-slate-600 dark:text-slate-400 text-sm">Showing Records</div>
                                <div className="text-lg font-bold dark:text-slate-100">{filteredTransactions.length}</div>
                            </div>
                            <div>
                                <div className="text-slate-600 dark:text-slate-400 text-sm">Total Gross</div>
                                <div className="text-lg font-bold dark:text-slate-100">
                                    Rp {filteredTransactions.reduce((sum, tx) => sum + Number(tx.gross_amount), 0).toLocaleString('id-ID')}
                                </div>
                            </div>
                            <div>
                                <div className="text-slate-600 dark:text-slate-400 text-sm">Total Discount</div>
                                <div className="text-lg font-bold text-orange-600 dark:text-orange-400">
                                    Rp {filteredTransactions.reduce((sum, tx) => sum + Number(tx.discount_amount || 0), 0).toLocaleString('id-ID')}
                                </div>
                            </div>
                            <div>
                                <div className="text-slate-600 dark:text-slate-400 text-sm">Total Net</div>
                                <div className="text-lg font-bold text-green-600 dark:text-green-400">
                                    Rp {filteredTransactions.reduce((sum, tx) => sum + Number(tx.net_amount), 0).toLocaleString('id-ID')}
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </AdminLayout>
    );
}
