import React, { useMemo } from 'react';
import { router } from '@inertiajs/react';
import {
    useReactTable,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    flexRender,
} from '@tanstack/react-table';
import { ChevronLeftIcon, ChevronRightIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/react/24/outline';

export default function DataTable({ columns, data, pagination, onPaginationChange, routeName, filters }) {
    // Ensure filters is always an object
    const safeFilters = filters || {};
    // Memoize columns and data to avoid unnecessary re-renders
    const memoColumns = useMemo(() => columns || [], [columns]);
    const memoData = useMemo(() => {
        // Handle both array and paginated object formats
        if (Array.isArray(data)) {
            return data;
        }
        if (data && Array.isArray(data.data)) {
            return data.data;
        }
        return data ? [data] : [];
    }, [data]);
    // Loading / empty states
    if (data === undefined || data === null) {
        return (
            <div className="p-6 text-center">
                <div className="inline-flex items-center gap-3">
                    <svg className="animate-spin h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a12 12 0 00-12 12h4z"></path>
                    </svg>
                    <span className="text-sm text-slate-700">Loading...</span>
                </div>
            </div>
        );
    }

    if (memoData.length === 0) {
        return (
            <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6 text-center text-slate-500 dark:text-slate-400">
                No data found. Try adjusting your filters or creating new records.
            </div>
        );
    }

    const handlePageChange = (newPage) => {
        if (routeName) {
            // Build query string with page parameter
            const params = new URLSearchParams({
                ...safeFilters,
                page: newPage
            });
            router.get(`?${params.toString()}`, { preserveState: true, replace: true });
        }
    };

    const handlePageSizeChange = (newSize) => {
        if (routeName) {
            // Build query string with per_page parameter
            const params = new URLSearchParams({
                ...safeFilters,
                per_page: newSize,
                page: 1
            });
            router.get(`?${params.toString()}`, { preserveState: true, replace: true });
        }
    };

    const pageCount = pagination && (pagination.last_page ?? (pagination.total && pagination.per_page ? Math.ceil(pagination.total / pagination.per_page) : undefined));

    const table = useReactTable({
        data: memoData,
        columns: memoColumns,
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
        ...(pagination && {
            manualPagination: true,
            ...(pageCount !== undefined ? { pageCount } : {}),
            state: {
                pagination: {
                    pageIndex: (pagination.current_page ?? 1) - 1,
                    pageSize: pagination.per_page ?? 10,
                },
            },
            onPaginationChange: (updater) => {
                if (!onPaginationChange) return;

                // Use provided pagination as previous state to avoid circular refs
                const prev = {
                    pageIndex: (pagination.current_page ?? 1) - 1,
                    pageSize: pagination.per_page ?? 10,
                };

                const newState = typeof updater === 'function' ? updater(prev) : updater;

                onPaginationChange({
                    page: (newState.pageIndex ?? 0) + 1,
                    per_page: newState.pageSize,
                });
            },
        }),
    });

    return (
        <div className="space-y-4">
            {/* Table */}
            <div className="overflow-x-auto bg-white dark:bg-slate-800 rounded-lg shadow">
                <table className="min-w-full divide-y divide-slate-200 dark:divide-slate-700" role="table">
                    <thead className="bg-slate-50 dark:bg-slate-700">
                        {table.getHeaderGroups().map((headerGroup) => (
                            <tr key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <th
                                        key={header.id}
                                        onClick={header.column.getCanSort() ? header.column.getToggleSortingHandler() : undefined}
                                        scope="col"
                                        aria-sort={header.column.getIsSorted() ? (header.column.getIsSorted() === 'asc' ? 'ascending' : 'descending') : 'none'}
                                        className={`px-6 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase tracking-wider ${header.column.getCanSort() ? 'cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700' : ''}`}
                                    >
                                        <div className="flex items-center justify-between">
                                            {flexRender(
                                                header.column.columnDef.header,
                                                header.getContext()
                                            )}
                                            {header.column.getIsSorted() && (
                                                <span className="ml-2">
                                                    {header.column.getIsSorted() === 'asc' ? (
                                                        <ChevronUpIcon className="h-4 w-4" />
                                                    ) : (
                                                        <ChevronDownIcon className="h-4 w-4" />
                                                    )}
                                                </span>
                                            )}
                                        </div>
                                    </th>
                                ))}
                            </tr>
                        ))}
                    </thead>
                    <tbody className="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                        {table.getRowModel().rows.map((row) => (
                            <tr key={row.id} className="hover:bg-slate-50 dark:hover:bg-slate-700">
                                {row.getVisibleCells().map((cell) => (
                                    <td key={cell.id} className="px-6 py-4 whitespace-nowrap text-sm text-slate-900 dark:text-slate-100">
                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                    </td>
                                ))}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            {pagination && (
                <div className="flex items-center justify-between px-4 py-3 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 sm:px-6 rounded-lg shadow">
                    <div className="flex justify-between flex-1 sm:hidden">
                        <button
                            onClick={() => handlePageChange(pagination.current_page - 1)}
                            disabled={!pagination.prev_page_url}
                            className="relative inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50"
                        >
                            Previous
                        </button>
                        <button
                            onClick={() => handlePageChange(pagination.current_page + 1)}
                            disabled={!pagination.next_page_url}
                            className="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-md hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                    <div className="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p className="text-sm text-slate-700 dark:text-slate-200">
                                Showing <span className="font-medium">{pagination.from}</span> to{' '}
                                <span className="font-medium">{pagination.to}</span> of{' '}
                                <span className="font-medium">{pagination.total}</span> results
                            </p>
                        </div>
                        <div>
                            <nav className="inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <button
                                        onClick={() => handlePageChange(1)}
                                        disabled={!pagination.prev_page_url}
                                        title="First page"
                                        className="relative inline-flex items-center rounded-l-md px-2 py-2 text-slate-400 dark:text-slate-500 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                                    >
                                        <span className="sr-only">First</span>
                                        <ChevronLeftIcon className="h-5 w-5" aria-hidden="true" />
                                    </button>
                                <span className="relative inline-flex items-center px-4 py-2 text-sm font-semibold">
                                    <span className="text-slate-900 dark:text-slate-100">Page {pagination.current_page} of {pagination.last_page ?? pageCount ?? '—'}</span>
                                </span>
                                <button
                                    onClick={() => handlePageChange(pagination.last_page ?? pageCount ?? 1)}
                                    disabled={!pagination.next_page_url}
                                    title="Last page"
                                    className="relative inline-flex items-center rounded-r-md px-2 py-2 text-slate-400 dark:text-slate-500 ring-1 ring-inset ring-slate-300 dark:ring-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 focus:z-20 focus:outline-offset-0 disabled:opacity-50"
                                >
                                    <span className="sr-only">Next</span>
                                    <ChevronRightIcon className="h-5 w-5" aria-hidden="true" />
                                </button>
                                {/* Page size selector */}
                                <select
                                    value={pagination.per_page}
                                    onChange={(e) => handlePageSizeChange(Number(e.target.value))}
                                    className="ml-3 rounded-md border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 dark:text-slate-100 text-sm px-2 py-1"
                                >
                                    {[10,25,50,100].map((s) => (
                                        <option key={s} value={s}>{s} / page</option>
                                    ))}
                                </select>
                            </nav>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
