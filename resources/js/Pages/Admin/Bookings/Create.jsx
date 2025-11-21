import { useState } from 'react';
import { useForm, router, Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { PlusIcon, TrashIcon } from '@heroicons/react/24/outline';
import toast from 'react-hot-toast';

export default function Create({ auth, products }) {
    const { data, setData, post, processing, errors } = useForm({
        customer_name: '',
        customer_phone: '',
        checkin_date: '',
        checkout_date: '',
        notes: '',
        units: [],
    });

    const addUnit = () => {
        setData('units', [...data.units, {
            product_id: '',
            quantity: 1,
            unit_price: 0,
        }]);
    };

    const removeUnit = (index) => {
        setData('units', data.units.filter((_, i) => i !== index));
    };

    const updateUnit = (index, field, value) => {
        const newUnits = [...data.units];
        newUnits[index][field] = value;
        
        if (field === 'product_id' && products) {
            const product = products.find(p => p.id == value);
            if (product) {
                newUnits[index].unit_price = product.base_price;
            }
        }
        
        setData('units', newUnits);
    };

    const calculateTotal = () => {
        return data.units.reduce((sum, unit) => {
            return sum + (unit.quantity * unit.unit_price);
        }, 0);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        if (data.units.length === 0) {
            toast.error('Please add at least one product');
            return;
        }
        
        post(route('admin.bookings.store'), {
            onSuccess: () => toast.success('Booking created successfully'),
            onError: () => toast.error('Failed to create booking'),
        });
    };

    return (
        <AdminLayout auth={auth}>
            <Head title="Create Booking" />
            
            <div className="py-6">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="mb-6">
                        <h1 className="text-2xl font-semibold text-gray-900">Create New Booking</h1>
                        <p className="mt-1 text-sm text-gray-600">Enter customer and booking details</p>
                    </div>

                    <form onSubmit={handleSubmit} className="space-y-6">
                        {/* Customer Information */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Customer Information</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Customer Name <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        value={data.customer_name}
                                        onChange={e => setData('customer_name', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    {errors.customer_name && <p className="mt-1 text-sm text-red-600">{errors.customer_name}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Phone <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        value={data.customer_phone}
                                        onChange={e => setData('customer_phone', e.target.value)}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                    {errors.customer_phone && <p className="mt-1 text-sm text-red-600">{errors.customer_phone}</p>}
                                </div>
                            </div>
                        </div>

                        {/* Booking Details */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Booking Details</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Check-in Date <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={data.checkin_date}
                                        onChange={e => setData('checkin_date', e.target.value)}
                                        min={new Date().toISOString().split('T')[0]}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-1">
                                        Check-out Date <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        value={data.checkout_date}
                                        onChange={e => setData('checkout_date', e.target.value)}
                                        min={data.checkin_date || new Date().toISOString().split('T')[0]}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        required
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                    <textarea
                                        value={data.notes}
                                        onChange={e => setData('notes', e.target.value)}
                                        rows={3}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Products */}
                        <div className="bg-white shadow rounded-lg p-6">
                            <div className="flex justify-between items-center mb-4">
                                <h2 className="text-lg font-medium text-gray-900">Products</h2>
                                <button
                                    type="button"
                                    onClick={addUnit}
                                    className="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    <PlusIcon className="h-5 w-5 mr-1" />
                                    Add Product
                                </button>
                            </div>

                            <div className="space-y-3">
                                {data.units.map((unit, index) => (
                                    <div key={index} className="flex gap-3 items-start">
                                        <div className="flex-1">
                                            <select
                                                value={unit.product_id}
                                                onChange={e => updateUnit(index, 'product_id', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                required
                                            >
                                                <option value="">Select Product</option>
                                                {products?.map(product => (
                                                    <option key={product.id} value={product.id}>
                                                        {product.name} - Rp {Number(product.base_price).toLocaleString('id-ID')}
                                                    </option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="w-24">
                                            <input
                                                type="number"
                                                value={unit.quantity}
                                                onChange={e => updateUnit(index, 'quantity', parseInt(e.target.value) || 1)}
                                                min="1"
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Qty"
                                                required
                                            />
                                        </div>
                                        <div className="w-32">
                                            <input
                                                type="number"
                                                value={unit.unit_price}
                                                onChange={e => updateUnit(index, 'unit_price', parseFloat(e.target.value) || 0)}
                                                min="0"
                                                step="1000"
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                placeholder="Price"
                                                required
                                            />
                                        </div>
                                        <div className="w-32 text-right pt-2">
                                            Rp {(unit.quantity * unit.unit_price).toLocaleString('id-ID')}
                                        </div>
                                        <button
                                            type="button"
                                            onClick={() => removeUnit(index)}
                                            className="text-red-600 hover:text-red-900 p-2"
                                        >
                                            <TrashIcon className="h-5 w-5" />
                                        </button>
                                    </div>
                                ))}
                            </div>

                            {data.units.length === 0 && (
                                <p className="text-sm text-gray-500 text-center py-4">
                                    No products added. Click "Add Product" to start.
                                </p>
                            )}

                            {data.units.length > 0 && (
                                <div className="mt-4 pt-4 border-t border-gray-200">
                                    <div className="flex justify-end text-lg font-semibold">
                                        Total: Rp {calculateTotal().toLocaleString('id-ID')}
                                    </div>
                                </div>
                            )}
                        </div>

                        {/* Actions */}
                        <div className="flex justify-end space-x-3">
                            <button
                                type="button"
                                onClick={() => router.get(route('admin.bookings.index'))}
                                className="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
                            >
                                {processing ? 'Creating...' : 'Create Booking'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
