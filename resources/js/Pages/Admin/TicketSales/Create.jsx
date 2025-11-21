import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { TrashIcon, PlusIcon } from '@heroicons/react/24/outline';

export default function Create({ auth, products }) {
    const [cart, setCart] = useState([]);
    const [selectedProduct, setSelectedProduct] = useState('');
    const [quantity, setQuantity] = useState(1);
    const [discountAmount, setDiscountAmount] = useState(0);
    const [processing, setProcessing] = useState(false);

    const addToCart = () => {
        if (!selectedProduct) return;
        
        const product = products.find(p => p.id == selectedProduct);
        if (!product) return;

        const existing = cart.find(item => item.product_id == selectedProduct);
        if (existing) {
            setCart(cart.map(item => 
                item.product_id == selectedProduct 
                    ? {...item, quantity: item.quantity + quantity}
                    : item
            ));
        } else {
            setCart([...cart, {
                product_id: product.id,
                product_name: product.name,
                unit_price: parseFloat(product.base_price),
                quantity: quantity,
            }]);
        }
        
        setSelectedProduct('');
        setQuantity(1);
    };

    const removeFromCart = (productId) => {
        setCart(cart.filter(item => item.product_id !== productId));
    };

    const updateQuantity = (productId, newQty) => {
        if (newQty < 1) return;
        setCart(cart.map(item => 
            item.product_id === productId ? {...item, quantity: newQty} : item
        ));
    };

    const grossAmount = cart.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
    const netAmount = grossAmount - (discountAmount || 0);
    const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);

    const handleSubmit = (e) => {
        e.preventDefault();
        
        setProcessing(true);
        
        router.post(route('admin.ticket-sales.store'), {
            items: cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                unit_price: item.unit_price,
            })),
            discount_amount: discountAmount || 0,
        }, {
            onSuccess: () => {
                setCart([]);
                setDiscountAmount(0);
            },
            onFinish: () => setProcessing(false),
        });
    };

    return (
        <AdminLayout auth={auth} title="New Ticket Sale">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold">New Ticket Sale</h1>
                <p className="text-sm text-gray-600">Point of Sale - Ticket Sales</p>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-3 gap-6">
                    {/* Product Selection */}
                    <div className="col-span-2 bg-white rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold mb-4">Add Products</h2>
                        <div className="flex gap-4 mb-6">
                            <select value={selectedProduct} onChange={e => setSelectedProduct(e.target.value)} className="flex-1 rounded-md border-gray-300">
                                <option value="">Select Product...</option>
                                {products.map(p => (
                                    <option key={p.id} value={p.id}>{p.name} - Rp {parseFloat(p.base_price).toLocaleString()}</option>
                                ))}
                            </select>
                            <input type="number" min="1" value={quantity} onChange={e => setQuantity(parseInt(e.target.value))} className="w-24 rounded-md border-gray-300" />
                            <button type="button" onClick={addToCart} className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2">
                                <PlusIcon className="w-5 h-5" /> Add
                            </button>
                        </div>

                        {/* Cart Table */}
                        <table className="w-full">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th className="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                    <th className="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th className="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                    <th className="px-4 py-2 text-xs font-medium text-gray-500 uppercase"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y">
                                {cart.length === 0 ? (
                                    <tr><td colSpan="5" className="px-4 py-8 text-center text-gray-500">No items in cart</td></tr>
                                ) : (
                                    cart.map(item => (
                                        <tr key={item.product_id}>
                                            <td className="px-4 py-3">{item.product_name}</td>
                                            <td className="px-4 py-3 text-right">Rp {item.unit_price.toLocaleString()}</td>
                                            <td className="px-4 py-3 text-center">
                                                <input type="number" min="1" value={item.quantity} onChange={e => updateQuantity(item.product_id, parseInt(e.target.value))} className="w-16 text-center rounded border-gray-300" />
                                            </td>
                                            <td className="px-4 py-3 text-right font-semibold">Rp {(item.unit_price * item.quantity).toLocaleString()}</td>
                                            <td className="px-4 py-3 text-center">
                                                <button type="button" onClick={() => removeFromCart(item.product_id)} className="text-red-600 hover:text-red-800">
                                                    <TrashIcon className="w-5 h-5" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Summary Panel */}
                    <div className="bg-white rounded-lg shadow p-6 h-fit">
                        <h2 className="text-lg font-semibold mb-4">Order Summary</h2>
                        
                        <div className="space-y-3 mb-4">
                            <div className="flex justify-between text-sm">
                                <span className="text-gray-600">Total Items:</span>
                                <span className="font-semibold">{totalQty}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-gray-600">Gross Amount:</span>
                                <span className="font-semibold">Rp {grossAmount.toLocaleString()}</span>
                            </div>
                            <div className="border-t pt-3">
                                <label className="block text-sm text-gray-600 mb-1">Discount (Rp):</label>
                                <input type="number" min="0" max={grossAmount} value={discountAmount} onChange={e => setDiscountAmount(parseFloat(e.target.value) || 0)} className="w-full rounded-md border-gray-300" placeholder="0" />
                            </div>
                            <div className="border-t pt-3">
                                <div className="flex justify-between text-lg font-bold">
                                    <span>Net Amount:</span>
                                    <span className="text-green-600">Rp {netAmount.toLocaleString()}</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" disabled={cart.length === 0 || processing} className="w-full py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed font-semibold">
                            {processing ? 'Processing...' : 'Complete Sale'}
                        </button>
                        
                        <button type="button" onClick={() => router.visit(route('admin.ticket-sales.index'))} className="w-full mt-2 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
