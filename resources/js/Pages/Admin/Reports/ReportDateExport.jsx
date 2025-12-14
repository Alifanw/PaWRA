import { useState } from 'react';

export default function ReportDateExport({ filters = {}, onFilterChange = () => {}, showVehicleTypeFilter = false, showStatusFilter = false }) {
    const [start, setStart] = useState(filters.start_date || '');
    const [end, setEnd] = useState(filters.end_date || '');
    const [vehicleType, setVehicleType] = useState(filters.vehicle_type || '');
    const [status, setStatus] = useState(filters.status || '');

    const applyFilter = (newStart, newEnd, newVehicleType = vehicleType, newStatus = status) => {
        const filterObj = { start_date: newStart, end_date: newEnd };
        if (showVehicleTypeFilter && newVehicleType) filterObj.vehicle_type = newVehicleType;
        if (showStatusFilter && newStatus) filterObj.status = newStatus;
        onFilterChange(filterObj);
    };

    return (
        <div className="flex items-center gap-3 flex-wrap mb-6 bg-white dark:bg-slate-800 rounded-lg shadow p-4">
            <div className="flex flex-col">
                <label htmlFor="report-start-date" className="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Start Date</label>
                <input 
                    id="report-start-date" 
                    name="start_date" 
                    type="date" 
                    value={start} 
                    onChange={e => { 
                        setStart(e.target.value); 
                        applyFilter(e.target.value, end); 
                    }} 
                    className="rounded-md border border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 px-3 py-2" 
                />
            </div>
            <div className="flex flex-col">
                <label htmlFor="report-end-date" className="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">End Date</label>
                <input 
                    id="report-end-date" 
                    name="end_date" 
                    type="date" 
                    value={end} 
                    onChange={e => { 
                        setEnd(e.target.value); 
                        applyFilter(start, e.target.value); 
                    }} 
                    className="rounded-md border border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 px-3 py-2" 
                />
            </div>
            
            {showVehicleTypeFilter && (
                <div className="flex flex-col">
                    <label htmlFor="vehicle-type" className="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vehicle Type</label>
                    <select 
                        id="vehicle-type"
                        value={vehicleType} 
                        onChange={e => { 
                            setVehicleType(e.target.value); 
                            applyFilter(start, end, e.target.value); 
                        }} 
                        className="rounded-md border border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 px-3 py-2"
                    >
                        <option value="">All Types</option>
                        <option value="roda2">Motorcycle</option>
                        <option value="roda4_6">Car</option>
                    </select>
                </div>
            )}

            {showStatusFilter && (
                <div className="flex flex-col">
                    <label htmlFor="status" className="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select 
                        id="status"
                        value={status} 
                        onChange={e => { 
                            setStatus(e.target.value); 
                            applyFilter(start, end, vehicleType, e.target.value); 
                        }} 
                        className="rounded-md border border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 px-3 py-2"
                    >
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            )}
        </div>
    );
}
