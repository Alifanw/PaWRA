import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// Default pricing (200 Rp for roda2, 500 Rp for roda4_6)
const PRICING = {
    roda2: 200,
    roda4_6: 500,
};

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        vehicle_number: '',
        vehicle_type: 'roda2',
        vehicle_count: 1,
        total_amount: 0,
        notes: '',
    });

    const [calculatedTotal, setCalculatedTotal] = useState(0);

    useEffect(() => {
        const unitPrice = PRICING[data.vehicle_type] || PRICING.roda2;
        const count = parseInt(data.vehicle_count) || 1;
        const total = unitPrice * count;
        setCalculatedTotal(total);
        if (!data.total_amount) {
            setData('total_amount', total);
        }
    }, [data.vehicle_type, data.vehicle_count]);

    const submit = (e) => {
        e.preventDefault();
        post('/admin/parking');
    };

    const unitPrice = PRICING[data.vehicle_type] || PRICING.roda2;

    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">New Parking Transaction</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Create a new parking transaction</p>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow max-w-2xl">
                <form onSubmit={submit}>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label htmlFor="vehicle-number" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Vehicle Number</label>
                            <input id="vehicle-number" name="vehicle_number" value={data.vehicle_number} onChange={e => setData('vehicle_number', e.target.value)} className="mt-1 w-full rounded border border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100" />
                        </div>
                        <div>
                            <label htmlFor="vehicle-count" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Vehicle Count</label>
                            <input id="vehicle-count" name="vehicle_count" type="number" value={data.vehicle_count} onChange={e => setData('vehicle_count', e.target.value)} min="1" className="mt-1 w-full rounded border border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100" />
                        </div>
                        <div>
                            <label htmlFor="vehicle-type" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Vehicle Type</label>
                            <select id="vehicle-type" name="vehicle_type" value={data.vehicle_type} onChange={e => setData('vehicle_type', e.target.value)} className="mt-1 w-full rounded border border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100">
                                <option value="roda2">Roda 2 (Motorcycle)</option>
                                <option value="roda4_6">Roda 4 & 6 (Car)</option>
                            </select>
                        </div>
                        <div>
                            <label htmlFor="unit-price" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Unit Price (Rp)</label>
                            <input id="unit-price" type="text" disabled value={unitPrice.toLocaleString('id-ID')} className="mt-1 w-full rounded border border-slate-300 px-3 py-2 bg-slate-100 dark:bg-slate-600 dark:border-slate-500 dark:text-slate-100" />
                        </div>
                        <div className="col-span-2">
                            <label htmlFor="total-amount" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Total Amount (Rp)</label>
                            <input id="total-amount" name="total_amount" type="number" value={data.total_amount} onChange={e => setData('total_amount', e.target.value)} className="mt-1 w-full rounded border border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100" />
                            <p className="mt-1 text-sm text-slate-500">Calculated: Rp {calculatedTotal.toLocaleString('id-ID')}</p>
                        </div>
                        <div className="col-span-2">
                            <label htmlFor="parking-notes" className="block text-sm font-medium text-slate-700 dark:text-slate-300">Notes</label>
                            <textarea id="parking-notes" name="notes" value={data.notes} onChange={e => setData('notes', e.target.value)} rows="3" className="mt-1 w-full rounded border border-slate-300 px-3 py-2 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100" />
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end space-x-3">
                        <button type="button" onClick={() => window.history.back()} className="px-4 py-2 border border-slate-300 rounded text-slate-700 dark:text-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700">
                            Cancel
                        </button>
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50">
                            {processing ? 'Creating...' : 'Create Transaction'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}

