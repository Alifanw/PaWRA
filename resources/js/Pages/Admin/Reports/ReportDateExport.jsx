import { useState } from 'react';

export default function ReportDateExport({ filters = {}, onFilterChange = () => {} }) {
    const [start, setStart] = useState(filters.startDate || '');
    const [end, setEnd] = useState(filters.endDate || '');

    const applyFilter = (newStart, newEnd) => {
        onFilterChange({ start_date: newStart, end_date: newEnd });
    };

    const onExport = (format = 'xlsx') => {
        // Use Inertia route() helper which is injected globally
        if (format === 'xlsx') {
            const url = route('admin.reports.export-all-xlsx', { start_date: start, end_date: end });
            window.open(url, '_blank');
        } else {
            const url = route('admin.reports.export-all', { start_date: start, end_date: end });
            window.open(url, '_blank');
        }
    };

    return (
        <div className="flex items-center gap-3 flex-wrap">
            <div className="flex flex-col">
                <label htmlFor="report-start-date" className="sr-only">Start date</label>
                <input id="report-start-date" name="start_date" type="date" value={start} onChange={e => { setStart(e.target.value); applyFilter(e.target.value, end); }} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" />
            </div>
            <div className="flex flex-col">
                <label htmlFor="report-end-date" className="sr-only">End date</label>
                <input id="report-end-date" name="end_date" type="date" value={end} onChange={e => { setEnd(e.target.value); applyFilter(start, e.target.value); }} className="rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600" />
            </div>
            <button type="button" onClick={() => onExport('xlsx')} className="inline-flex items-center px-3 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition">Export .xlsx</button>
            <button type="button" onClick={() => onExport('csv')} className="inline-flex items-center px-3 py-2 rounded-md bg-slate-700 text-white hover:bg-slate-800 transition">Export CSV</button>
        </div>
    );
}
