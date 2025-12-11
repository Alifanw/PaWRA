import { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { TrashIcon, PlusIcon } from '@heroicons/react/24/outline';


export default function Create({ auth, products }) {
    const [cart, setCart] = useState([]);
    const [selectedProduct, setSelectedProduct] = useState('');
    const [quantity, setQuantity] = useState(1);
    const [processing, setProcessing] = useState(false);
    const [paymentMethod, setPaymentMethod] = useState('cash');
    const [paymentReference, setPaymentReference] = useState('');

    // Tambah produk ke cart, default diskon 0%
    const addToCart = () => {
        if (!selectedProduct) return;
        const product = products.find(p => p.id == selectedProduct);
        if (!product) return;

        const existing = cart.find(i => i.product_id == selectedProduct);
        if (existing) {
            setCart(cart.map(i =>
                i.product_id == selectedProduct
                    ? { ...i, quantity: i.quantity + quantity }
                    : i
            ));
        } else {
            setCart([
                ...cart,
                {
                    product_id: product.id,
                    product_name: product.name,
                    unit_price: parseFloat(product.base_price),
                    quantity,
                    discount: '' // diskon per item (%)
                }
            ]);
        }

        setSelectedProduct('');
        setQuantity(1);
    };

    const removeFromCart = (id) => {
        setCart(cart.filter(i => i.product_id !== id));
    };

    const updateQuantity = (id, qty) => {
        if (qty < 1) return;
        setCart(
            cart.map(i =>
                i.product_id === id ? { ...i, quantity: qty } : i
            )
        );
    };

    const updateDiscount = (id, value) => {
        setCart(
            cart.map(i =>
                i.product_id === id ? { ...i, discount: value } : i
            )
        );
    };

    // Subtotal per item (sudah diskon)
    const getSubtotal = (item) => {
        const qty = Number(item.quantity) || 0;
        const price = Number(item.unit_price) || 0;
        const d = item.discount === '' ? 0 : Number(item.discount) || 0;
        return qty * price - (qty * price * d / 100);
    };

    const grossAmount = cart.reduce(
        (sum, i) => sum + (Number(i.unit_price) * Number(i.quantity)),
        0
    );

    const netAmount = cart.reduce(
        (sum, i) => sum + getSubtotal(i),
        0
    );

    const totalQty = cart.reduce((sum, i) => sum + Number(i.quantity), 0);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (cart.length === 0) return;
        setProcessing(true);

        router.post(
            route('admin.ticket-sales.store'),
            {
                items: cart.map(i => ({
                    product_id: i.product_id,
                    unit_price: i.unit_price,
                    quantity: i.quantity,
                    discount: i.discount === '' ? 0 : Number(i.discount)
                })),
                payment_method: paymentMethod,
                payment_reference: paymentReference,
            },
            {
                onSuccess: () => {
                    setCart([]);
                    setPaymentReference('');
                    setPaymentMethod('cash');
                },
                onFinish: () => setProcessing(false)
            }
        );
    };

    return (
        <AdminLayout auth={auth} title="New Ticket Sale">
            <div className="mb-6">
                <h1 className="text-2xl font-semibold dark:text-slate-100">New Ticket Sale</h1>
                <p className="text-sm text-slate-600 dark:text-slate-400">Point of Sale - Ticket Sales</p>
            </div>

            <form onSubmit={handleSubmit}>
                <div className="grid grid-cols-3 gap-6">
                    <div className="col-span-2 bg-white dark:bg-slate-800 rounded-lg shadow p-6">
                        <h2 className="text-lg font-semibold mb-4 dark:text-slate-100">Add Products</h2>

                        <div className="flex gap-4 mb-6">
                            <label htmlFor="ts-product" className="sr-only">Product</label>
                            <select
                                id="ts-product"
                                name="product_id"
                                value={selectedProduct}
                                onChange={e => setSelectedProduct(e.target.value)}
                                className="flex-1 rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                            >
                                <option value="">Select Product...</option>
                                {products.map(p => (
                                    <option key={p.id} value={p.id}>
                                        {p.name} - Rp {parseFloat(p.base_price).toLocaleString()}
                                    </option>
                                ))}
                            </select>

                            <label htmlFor="ts-quantity" className="sr-only">Quantity</label>
                            <input
                                id="ts-quantity"
                                name="quantity"
                                type="number"
                                min="1"
                                value={quantity}
                                onChange={e => setQuantity(parseInt(e.target.value))}
                                className="w-24 rounded-md border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                            />

                            <button
                                type="button"
                                onClick={addToCart}
                                className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center gap-2"
                            >
                                <PlusIcon className="w-5 h-5" /> Add
                            </button>
                        </div>

                        <table className="w-full">
                            <thead className="bg-slate-50 dark:bg-slate-700">
                                <tr>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Product</th>
                                    <th className="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Price</th>
                                    <th className="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Qty</th>
                                    <th className="px-4 py-2 text-center text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Disc %</th>
                                    <th className="px-4 py-2 text-right text-xs font-medium text-slate-500 dark:text-slate-300 uppercase">Subtotal</th>
                                    <th className="px-4 py-2"></th>
                                </tr>
                            </thead>

                            <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                                {cart.length === 0 ? (
                                    <tr>
                                        <td colSpan="6" className="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                            No items in cart
                                        </td>
                                    </tr>
                                ) : (
                                    cart.map(item => (
                                        <tr key={item.product_id}>
                                            <td className="px-4 py-3">{item.product_name}</td>

                                            <td className="px-4 py-3 text-right">
                                                Rp {item.unit_price.toLocaleString()}
                                            </td>

                                            <td className="px-4 py-3 text-center">
                                                <input
                                                    id={`cart-qty-${item.product_id}`}
                                                    name={`items[${item.product_id}][quantity]`}
                                                    type="number"
                                                    min="1"
                                                    value={item.quantity}
                                                    onChange={e =>
                                                        updateQuantity(
                                                            item.product_id,
                                                            parseInt(e.target.value)
                                                        )
                                                    }
                                                    className="w-16 text-center rounded border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                                                />
                                            </td>

                                            <td className="px-4 py-3 text-center">
                                                <input
                                                    id={`cart-discount-${item.product_id}`}
                                                    name={`items[${item.product_id}][discount]`}
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    step="0.1"
                                                    value={item.discount === '' ? '' : item.discount}
                                                    onChange={e => {
                                                        const raw = e.target.value;
                                                        if (raw === '') {
                                                            updateDiscount(item.product_id, '');
                                                            return;
                                                        }
                                                        const num = parseFloat(raw);
                                                        if (!isNaN(num) && num >= 0 && num <= 100) {
                                                            updateDiscount(item.product_id, num);
                                                        }
                                                    }}
                                                    className="w-16 text-center rounded border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600"
                                                    placeholder="Disc %"
                                                />
                                            </td>

                                            <td className="px-4 py-3 text-right font-semibold text-slate-900 dark:text-slate-100">
                                                Rp {getSubtotal(item).toLocaleString()}
                                            </td>

                                            <td className="px-4 py-3 text-center">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        removeFromCart(item.product_id)
                                                    }
                                                    className="text-red-600 hover:text-red-800"
                                                >
                                                    <TrashIcon className="w-5 h-5" />
                                                </button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6 h-fit">
                        <h2 className="text-lg font-semibold mb-4 dark:text-slate-100">Order Summary</h2>

                        <div className="space-y-3 mb-4">
                            <div className="flex justify-between text-sm">
                                <span className="text-slate-600 dark:text-slate-400">Total Items:</span>
                                <span className="font-semibold dark:text-slate-100">{totalQty}</span>
                            </div>

                            <div className="flex justify-between">
                                <span className="text-slate-600 dark:text-slate-400">Gross Amount:</span>
                                <span className="font-semibold dark:text-slate-100">Rp {grossAmount.toLocaleString()}</span>
                            </div>

                            <div className="border-t border-slate-200 dark:border-slate-700 pt-3">
                                <div className="flex justify-between text-lg font-bold">
                                    <span className="dark:text-slate-100">Net Amount:</span>
                                    <span className="text-green-600 dark:text-green-400">
                                        Rp {netAmount.toLocaleString()}
                                    </span>
                                </div>
                            </div>

                            <div className="mt-4">
                                <label htmlFor="ts-payment-method" className="block text-sm text-slate-700 dark:text-slate-300">Payment Method</label>
                                <select id="ts-payment-method" name="payment_method" value={paymentMethod} onChange={e => setPaymentMethod(e.target.value)} className="w-full rounded-md mt-1">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="e_wallet">E-Wallet</option>
                                </select>
                            </div>

                            <div className="mt-3">
                                <label htmlFor="ts-payment-reference" className="block text-sm text-slate-700 dark:text-slate-300">Payment Reference (optional)</label>
                                <input id="ts-payment-reference" name="payment_reference" type="text" value={paymentReference} onChange={e => setPaymentReference(e.target.value)} className="w-full rounded-md mt-1" />
                            </div>

                        </div>

                        <button
                            type="submit"
                            disabled={cart.length === 0 || processing}
                            className="w-full py-3 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-slate-300 font-semibold"
                        >
                            {processing ? 'Processing...' : 'Complete Sale'}
                        </button>

                        <button
                            type="button"
                            onClick={() =>
                                router.visit(route('admin.ticket-sales.index'))
                            }
                            className="w-full mt-2 py-2 border border-slate-300 rounded-md hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700 dark:text-slate-100"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
