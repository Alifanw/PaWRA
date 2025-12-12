import { useState, useCallback } from 'react';

export function useBulkSelection() {
    const [selectedIds, setSelectedIds] = useState([]);
    const [selectAllChecked, setSelectAllChecked] = useState(false);

    const toggleSelection = useCallback((id) => {
        setSelectedIds((prev) =>
            prev.includes(id) ? prev.filter((item) => item !== id) : [...prev, id]
        );
        // Reset select all when toggling individual items
        if (selectAllChecked) {
            setSelectAllChecked(false);
        }
    }, [selectAllChecked]);

    const toggleSelectAll = useCallback((data) => {
        if (selectAllChecked) {
            setSelectedIds([]);
            setSelectAllChecked(false);
        } else {
            const ids = data.map((item) => item.id);
            setSelectedIds(ids);
            setSelectAllChecked(true);
        }
    }, [selectAllChecked]);

    const clearSelection = useCallback(() => {
        setSelectedIds([]);
        setSelectAllChecked(false);
    }, []);

    const isSelected = useCallback((id) => {
        return selectedIds.includes(id);
    }, [selectedIds]);

    return {
        selectedIds,
        selectAllChecked,
        toggleSelection,
        toggleSelectAll,
        clearSelection,
        isSelected,
        selectedCount: selectedIds.length,
    };
}
