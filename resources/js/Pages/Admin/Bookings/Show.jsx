import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import toast from 'react-hot-toast';

export default function Show({ auth, booking }) {
  // Safe route helper: try Ziggy's `route()` and fall back to manual URL
  const safeRoute = (name, ...params) => {
    try {
      // global route() provided by Ziggy when @routes is present
      // eslint-disable-next-line no-undef
      return typeof route === 'function' ? route(name, ...params) : null;
    } catch (e) {
      // fallback: build a sensible RESTful path for our admin routes
      // examples: admin.bookings.payments.store => /admin/bookings/{id}/payments
      if (name === 'admin.bookings.payments.store') {
        const bookingId = params[0] ?? booking?.id ?? '';
        return `/admin/bookings/${bookingId}/payments`;
      }
      if (name === 'admin.payments.print') {
        const paymentId = params[0] ?? '';
        return `/admin/payments/${paymentId}/print`;
      }
      // default: return name so at least something is returned
      return name;
    }
  };

  const { data, setData, post, processing, errors } = useForm({
    amount: Number(booking?.total_amount ?? 0),
    payment_method: 'cash',
    notes: '',
  });

  const submitPayment = (e) => {
    e.preventDefault();
    // open a blank window synchronously to avoid popup blockers
    let printWindow = null;
    try {
      printWindow = window.open('', '_blank', 'width=900,height=700');
      if (printWindow) {
        printWindow.document.write('<html><head><title>Kwitansi</title></head><body><p>Loading receipt...</p></body></html>');
        printWindow.document.close();
      }
    } catch (err) {
      printWindow = null;
    }

    // ensure amount is numeric before sending
    const payload = { ...data, amount: Number(data.amount) };

    // send as JSON so controller returns 'receipt_html'
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch(route('admin.bookings.payments.store', booking.id), {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf || '',
      },
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    })
      .then(async (res) => {
        if (!res.ok) throw await res.text();
        return res.json();
      })
      .then((json) => {
        toast.success('Payment recorded');
        if (printWindow) {
          try {
            printWindow.document.open();
            printWindow.document.write(json.receipt_html || '<p>No receipt</p>');
            printWindow.document.close();
            printWindow.focus();
            // give browser a moment to load resources then call print
            setTimeout(() => {
              try { printWindow.print(); } catch (e) { /* ignore */ }
            }, 600);
          } catch (e) {
            // fallback: open new tab with print route
            try { window.open(`/admin/payments/${json.payment_id}/print`, '_blank'); } catch (err) { /* ignore */ }
          }
        } else {
          // no popup opened, fallback to opening print route in new tab
          try { window.open(`/admin/payments/${json.payment_id}/print`, '_blank'); } catch (e) { /* ignore */ }
        }
      })
      .catch((err) => {
        toast.error('Failed to record payment');
        if (printWindow) try { printWindow.close(); } catch (e) { /* ignore */ }
      });
  };

  return (
    <AdminLayout auth={auth} title="Booking Details">
      <div className="mb-6">
        <h1 className="text-2xl font-semibold text-slate-900 dark:text-slate-100">Booking {booking?.booking_code}</h1>
        <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">Customer: {booking?.customer_name} — {booking?.customer_phone}</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
          <h2 className="text-lg font-medium mb-3">Rincian Booking</h2>
          <table className="w-full text-sm">
            <tbody>
              {booking?.units?.map((u, i) => (
                <tr key={i} className="border-b">
                  <td className="py-2">{u.product_name}</td>
                  <td className="py-2 text-right">{u.quantity} × Rp {Number(u.unit_price).toLocaleString('id-ID')}</td>
                </tr>
              ))}
            </tbody>
          </table>
          <div className="mt-4 space-y-2 pt-4 border-t">
            <div className="flex justify-between">
              <span>Total</span>
              <span className="font-medium">Rp {Number(booking?.total_amount || 0).toLocaleString('id-ID')}</span>
            </div>
            {booking?.dp_required && (
              <div className="flex justify-between text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900 bg-opacity-30 p-2 rounded">
                <span>DP Required</span>
                <span className="font-medium">Rp {Number(booking?.effective_dp_amount || 0).toLocaleString('id-ID')}</span>
              </div>
            )}
            <div className="flex justify-between font-semibold text-lg">
              <span>Sisa Pembayaran</span>
              <span>Rp {Number(booking?.remaining_balance || 0).toLocaleString('id-ID')}</span>
            </div>
            <div className="text-sm text-slate-600 dark:text-slate-400">
              Status: <span className="font-medium">{booking?.payment_status ?? '-'}</span>
            </div>
          </div>
        </div>

        <div className="bg-white dark:bg-slate-800 rounded-lg shadow p-6">
          <h2 className="text-lg font-medium mb-3">Pembayaran</h2>
          <form onSubmit={submitPayment} className="space-y-3">
            <div>
              <label htmlFor="payment-amount" className="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">Jumlah (Rp)</label>
              <input
                id="payment-amount"
                name="amount"
                type="text"
                value={data.amount}
                onChange={e => {
                  // sanitize input: remove thousand separators or stray characters
                  const raw = String(e.target.value);
                  const cleaned = raw.replace(/[^0-9\-\.]/g, '');
                  const num = cleaned === '' ? '' : Number(cleaned);
                  setData('amount', num);
                }}
                className="w-full rounded-md border border-slate-300 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              {errors.amount && <div className="text-red-600 text-sm">{errors.amount}</div>}
            </div>

            <div>
              <label htmlFor="payment-method" className="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">Metode</label>
              <select id="payment-method" name="payment_method" value={data.payment_method} onChange={e => setData('payment_method', e.target.value)} className="w-full rounded-md border border-slate-300 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="e_wallet">E-Wallet</option>
              </select>
            </div>

            <div>
              <label htmlFor="payment-notes" className="block text-sm font-medium text-slate-900 dark:text-slate-100 mb-1">Catatan</label>
              <input id="payment-notes" name="notes" type="text" value={data.notes} onChange={e => setData('notes', e.target.value)} className="w-full rounded-md border border-slate-300 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>

            <div className="flex justify-end">
              <button type="submit" disabled={processing} className="px-4 py-2 bg-blue-600 text-white rounded-md">
                {processing ? 'Processing...' : 'Record & Print Kwitansi'}
              </button>
            </div>
          </form>

          <div className="mt-4">
            
            <ul className="text-sm space-y-2">
              {booking?.payments?.map((p) => (
                <li key={p.id} className="flex justify-between">
                  <span>{p.payment_date} • {p.payment_method}</span>
                  <div className="flex items-center gap-3">
                    <span>Rp {Number(p.amount).toLocaleString('id-ID')}</span>
                    <button type="button" onClick={() => window.open(route('admin.payments.print', p.id), '_blank')} className="text-blue-600 text-xs">Print</button>
                  </div>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </AdminLayout>
  );
}
