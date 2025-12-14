import React from 'react';
import { TrashIcon, CheckIcon, XMarkIcon } from '@heroicons/react/24/outline';
import toast from 'react-hot-toast';

export default function BulkActionsToolbar({ selectedIds, onSelectAll, selectAllChecked, totalItems, onDeleteSelected, onCustomAction, isLoading, actions = [] }) {
    const selectedCount = selectedIds.length;
    const hasSelection = selectedCount > 0;

    const handleDeleteAll = () => {
        if (selectedCount === 0) {
            toast.error('No items selected');
            return;
        }

        if (confirm(`Are you sure you want to delete ${selectedCount} selected item(s)?`)) {
            // Call onDeleteSelected without waiting - let it handle its own state
            onDeleteSelected();
        }
    };

    if (!hasSelection && selectedCount === 0) {
        return null;
    }

    return (
        <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6 flex items-center justify-between">
            <div className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                    <input
                        type="checkbox"
                        checked={selectAllChecked}
                        onChange={onSelectAll}
                        className="rounded dark:bg-slate-700 dark:border-slate-600"
                        title={selectAllChecked ? "Deselect all" : "Select all"}
                    />
                    <span className="text-sm font-medium text-slate-700 dark:text-slate-300">
                        {selectedCount > 0 ? (
                            <>
                                {selectedCount} of {totalItems} selected
                                {selectAllChecked && totalItems > selectedCount && <span className="ml-2 text-xs text-slate-500">(All items on this page)</span>}
                            </>
                        ) : (
                            "No items selected"
                        )}
                    </span>
                </div>
            </div>

            <div className="flex items-center gap-2">
                {/* Custom actions */}
                {actions.map((action) => (
                    <button
                        key={action.id}
                        onClick={() => action.handler(selectedIds)}
                        disabled={isLoading || selectedCount === 0}
                        className="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        title={action.title}
                    >
                        {action.icon && <action.icon className="h-4 w-4" />}
                        {action.label}
                    </button>
                ))}

                {/* Delete button */}
                <button
                    onClick={handleDeleteAll}
                    disabled={selectedCount === 0 || isLoading}
                    className="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium rounded-md bg-red-600 hover:bg-red-700 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    title={selectedCount > 0 ? `Delete ${selectedCount} item(s)` : "Select items to delete"}
                >
                    <TrashIcon className="h-4 w-4" />
                    {isLoading ? (
                        <span className="inline-flex items-center gap-1">
                            <span className="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            Deleting...
                        </span>
                    ) : (
                        'Delete Selected'
                    )}
                </button>
            </div>
        </div>
    );
}
