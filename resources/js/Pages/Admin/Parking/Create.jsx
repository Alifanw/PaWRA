import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';

export default function Create({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        vehicle_number: '',
        vehicle_type: 'roda2',
        vehicle_count: 1,
        total_amount: 0,
        notes: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/admin/parking');
    };

    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">New Parking Transaction</h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Create a new parking transaction (uses API)</p>
                    </div>
                </div>
            </div>

            <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow max-w-2xl">
                <form onSubmit={submit}>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label htmlFor="vehicle-number" className="block text-sm text-slate-500">Vehicle Number</label>
                            <input id="vehicle-number" name="vehicle_number" value={data.vehicle_number} onChange={e => setData('vehicle_number', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label htmlFor="vehicle-count" className="block text-sm text-slate-500">Vehicle Count</label>
                            <input id="vehicle-count" name="vehicle_count" type="number" value={data.vehicle_count} onChange={e => setData('vehicle_count', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label htmlFor="vehicle-type" className="block text-sm text-slate-500">Vehicle Type</label>
                            <select id="vehicle-type" name="vehicle_type" value={data.vehicle_type} onChange={e => setData('vehicle_type', e.target.value)} className="mt-1 w-full rounded border px-3 py-2">
                                <option value="roda2">Roda 2</option>
                                <option value="roda4_6">Roda 4 & 6</option>
                            </select>
                        </div>
                        <div>
                            <label htmlFor="total-amount" className="block text-sm text-slate-500">Total Amount</label>
                            <input id="total-amount" name="total_amount" type="number" value={data.total_amount} onChange={e => setData('total_amount', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                        <div>
                            <label htmlFor="parking-notes" className="block text-sm text-slate-500">Notes</label>
                            <input id="parking-notes" name="notes" value={data.notes} onChange={e => setData('notes', e.target.value)} className="mt-1 w-full rounded border px-3 py-2" />
                        </div>
                    </div>

                    <div className="mt-4">
                        <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded">Submit</button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
