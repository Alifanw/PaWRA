import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import toast from 'react-hot-toast';

export default function Show({ auth, sale }) {
    const [showPaymentForm, setShowPaymentForm] = useState(false);
    const [showRefundForm, setShowRefundForm] = useState(false);

    const paymentForm = useForm({
        payment_method: 'cash',
        payment_reference: '',
        amount: '',
    });

    const refundForm = useForm({
        amount: '',
        reference: '',
    });

    const handlePayment = (e) => {
        e.preventDefault();
        paymentForm.post(route('admin.ticket-sales.pay', sale.id), {
            onSuccess: () => {
                setShowPaymentForm(false);
                paymentForm.reset();
                toast.success('Payment recorded');
                window.location.reload();
            },
            onError: (errors) => {
                toast.error(Object.values(errors)[0] || 'Error recording payment');
            }
        });
    };

    const handleRefund = (e) => {
        e.preventDefault();
        refundForm.post(route('admin.ticket-sales.refund', sale.id), {
            onSuccess: () => {
                setShowRefundForm(false);
                refundForm.reset();
                toast.success('Refund recorded');
                window.location.reload();
            },
            onError: (errors) => {
                toast.error(Object.values(errors)[0] || 'Error recording refund');
            }
        });
    };

    const handleCancel = () => {
        if (confirm('Are you sure you want to cancel this transaction?')) {
            const form = useForm({});
            form.post(route('admin.ticket-sales.cancel', sale.id), {
                onSuccess: () => {
                    toast.success('Transaction cancelled');
                    window.location.reload();
                },
                onError: (err) => {
                    toast.error('Cannot cancel: Transaction has payments. Refund first.');
                }
            });
        }
    };

    const totalPaid = sale.payments
        .filter(p => p.status === 'successful')
        .reduce((sum, p) => sum + Number(p.amount), 0);

    const totalRefunded = sale.payments
        .filter(p => p.status === 'refunded')
        .reduce((sum, p) => sum + Number(p.amount), 0);

    const balance = Math.max(0, sale.net_amount - totalPaid + totalRefunded);

    return (
        <AdminLayout auth={auth}>
            <div className="mb-6">
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">
                            Tiket {sale.invoice_no}
                        </h1>
                        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Detail dan pembayaran transaksi</p>
                    </div>
                    <div>
                        <Link href="/admin/ticket-sales" className="px-4 py-2 rounded-md bg-slate-100">Back</Link>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-3 gap-6">
                {/* Main content */}
                <div className="col-span-2 space-y-6">
                    {/* Transaction details */}
                    <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                        <h2 className="text-lg font-semibold mb-4 dark:text-slate-100">Detail Transaksi</h2>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <div className="text-sm text-slate-500">Invoice</div>
                                <div className="font-medium dark:text-slate-100">{sale.invoice_no}</div>
                            </div>
                            <div>
                                <div className="text-sm text-slate-500">Tanggal</div>
                                <div className="font-medium dark:text-slate-100">{new Date(sale.sale_date).toLocaleString()}</div>
                            </div>
                            <div>
                                <div className="text-sm text-slate-500">Kasir</div>
                                <div className="font-medium dark:text-slate-100">{sale.cashier_name}</div>
                            </div>
                            <div>
                                <div className="text-sm text-slate-500">Status</div>
                                <div className={`inline-flex px-2 py-1 rounded text-xs font-semibold ${
                                    sale.transaction_status === 'paid' ? 'bg-green-100 text-green-800' :
                                    sale.transaction_status === 'cancelled' ? 'bg-red-100 text-red-800' :
                                    'bg-yellow-100 text-yellow-800'
                                }`}>
                                    {sale.transaction_status}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Items list */}
                    <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                        <h2 className="text-lg font-semibold mb-4 dark:text-slate-100">Items</h2>
                        <table className="w-full text-sm">
                            <thead className="bg-slate-50 dark:bg-slate-700">
                                <tr>
                                    <th className="px-4 py-2 text-left">Product</th>
                                    <th className="px-4 py-2 text-right">Qty</th>
                                    <th className="px-4 py-2 text-right">Price</th>
                                    <th className="px-4 py-2 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                                {sale.items.map((item, idx) => (
                                    <tr key={idx}>
                                        <td className="px-4 py-2">{item.product_name}</td>
                                        <td className="px-4 py-2 text-right">{item.quantity}</td>
                                        <td className="px-4 py-2 text-right">Rp {Number(item.unit_price).toLocaleString('id-ID')}</td>
                                        <td className="px-4 py-2 text-right">Rp {Number(item.subtotal).toLocaleString('id-ID')}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Payments history */}
                    <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                        <h2 className="text-lg font-semibold mb-4 dark:text-slate-100">Payment History</h2>
                        {sale.payments.length === 0 ? (
                            <p className="text-slate-500">No payments recorded</p>
                        ) : (
                            <table className="w-full text-sm">
                                <thead className="bg-slate-50 dark:bg-slate-700">
                                    <tr>
                                        <th className="px-4 py-2 text-left">Type</th>
                                        <th className="px-4 py-2 text-left">Method</th>
                                        <th className="px-4 py-2 text-right">Amount</th>
                                        <th className="px-4 py-2 text-left">Date</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                                    {sale.payments.map((p) => (
                                        <tr key={p.id} className={p.status === 'refunded' ? 'bg-red-50 dark:bg-red-950' : ''}>
                                            <td className="px-4 py-2">
                                                <span className={`text-xs font-semibold px-2 py-1 rounded ${
                                                    p.status === 'successful' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                                }`}>
                                                    {p.status}
                                                </span>
                                            </td>
                                            <td className="px-4 py-2">{p.method}</td>
                                            <td className="px-4 py-2 text-right font-semibold">Rp {Number(p.amount).toLocaleString('id-ID')}</td>
                                            <td className="px-4 py-2 text-sm text-slate-500">{new Date(p.created_at).toLocaleString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>

                {/* Sidebar - Summary & Actions */}
                <div className="space-y-4">
                    {/* Summary */}
                    <div className="bg-white dark:bg-slate-800 p-6 rounded-lg shadow">
                        <h2 className="text-lg font-semibold mb-4 dark:text-slate-100">Summary</h2>
                        <div className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-slate-600 dark:text-slate-400">Total Qty:</span>
                                <span className="font-semibold dark:text-slate-100">{sale.total_qty}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-slate-600 dark:text-slate-400">Gross:</span>
                                <span className="dark:text-slate-100">Rp {Number(sale.gross_amount).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-slate-600 dark:text-slate-400">Discount:</span>
                                <span className="dark:text-slate-100">Rp {Number(sale.discount_amount).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="border-t border-slate-200 dark:border-slate-700 pt-3 flex justify-between">
                                <span className="text-slate-600 dark:text-slate-400">Net Amount:</span>
                                <span className="font-semibold dark:text-slate-100">Rp {Number(sale.net_amount).toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-green-600 dark:text-green-400">
                                <span>Paid:</span>
                                <span className="font-semibold">Rp {totalPaid.toLocaleString('id-ID')}</span>
                            </div>
                            <div className="flex justify-between text-red-600 dark:text-red-400">
                                <span>Refunded:</span>
                                <span className="font-semibold">Rp {totalRefunded.toLocaleString('id-ID')}</span>
                            </div>
                            <div className="border-t border-slate-200 dark:border-slate-700 pt-3 flex justify-between">
                                <span className={balance > 0 ? 'text-slate-600 dark:text-slate-400' : 'text-green-600'}>Balance:</span>
                                <span className={`font-bold ${balance > 0 ? 'dark:text-slate-100' : 'text-green-600'}`}>
                                    Rp {balance.toLocaleString('id-ID')}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Actions */}
                    {sale.transaction_status !== 'cancelled' && (
                        <div className="space-y-2">
                            {sale.transaction_status !== 'paid' && balance > 0 && (
                                <button
                                    onClick={() => setShowPaymentForm(!showPaymentForm)}
                                    className="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-semibold"
                                >
                                    {showPaymentForm ? 'Close' : 'Record Payment'}
                                </button>
                            )}
                            {totalPaid > 0 && (
                                <button
                                    onClick={() => setShowRefundForm(!showRefundForm)}
                                    className="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-semibold"
                                >
                                    {showRefundForm ? 'Close' : 'Record Refund'}
                                </button>
                            )}
                            {totalPaid === 0 && (
                                <button
                                    onClick={handleCancel}
                                    className="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-semibold"
                                >
                                    Cancel Transaction
                                </button>
                            )}
                        </div>
                    )}

                    {/* Payment Form */}
                    {showPaymentForm && sale.transaction_status !== 'paid' && (
                        <form onSubmit={handlePayment} className="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                            <h3 className="font-semibold mb-3 dark:text-slate-100">Record Payment</h3>
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300">Method</label>
                                    <select value={paymentForm.data.payment_method} onChange={e => paymentForm.setData('payment_method', e.target.value)} className="w-full rounded-md text-sm mt-1">
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="e_wallet">E-Wallet</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300">Amount (leave empty for remaining)</label>
                                    <input type="number" step="0.01" value={paymentForm.data.amount} onChange={e => paymentForm.setData('amount', e.target.value)} className="w-full rounded-md text-sm mt-1" />
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300">Reference</label>
                                    <input type="text" value={paymentForm.data.payment_reference} onChange={e => paymentForm.setData('payment_reference', e.target.value)} className="w-full rounded-md text-sm mt-1" />
                                </div>
                                <button type="submit" disabled={paymentForm.processing} className="w-full py-2 bg-green-600 text-white rounded-md text-sm font-semibold hover:bg-green-700 disabled:opacity-50">
                                    {paymentForm.processing ? 'Recording...' : 'Record'}
                                </button>
                            </div>
                        </form>
                    )}

                    {/* Refund Form */}
                    {showRefundForm && totalPaid > 0 && (
                        <form onSubmit={handleRefund} className="bg-red-50 dark:bg-red-900 p-4 rounded-lg border border-red-200 dark:border-red-700">
                            <h3 className="font-semibold mb-3 dark:text-slate-100">Record Refund</h3>
                            <div className="space-y-3">
                                <div>
                                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300">Amount</label>
                                    <input type="number" step="0.01" value={refundForm.data.amount} onChange={e => refundForm.setData('amount', e.target.value)} className="w-full rounded-md text-sm mt-1" required />
                                </div>
                                <div>
                                    <label className="block text-xs font-medium text-slate-700 dark:text-slate-300">Reference</label>
                                    <input type="text" value={refundForm.data.reference} onChange={e => refundForm.setData('reference', e.target.value)} className="w-full rounded-md text-sm mt-1" />
                                </div>
                                <button type="submit" disabled={refundForm.processing} className="w-full py-2 bg-red-600 text-white rounded-md text-sm font-semibold hover:bg-red-700 disabled:opacity-50">
                                    {refundForm.processing ? 'Recording...' : 'Record'}
                                </button>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
