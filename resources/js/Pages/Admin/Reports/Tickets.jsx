import React, { useState } from 'react';
import { Head, usePage, Link } from '@inertiajs/react';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

export default function TicketsReport() {
    const { auth, tickets, stats, filters } = usePage().props;
    const [startDate, setStartDate] = useState(filters.start_date || '');
    const [endDate, setEndDate] = useState(filters.end_date || '');
    const [status, setStatus] = useState(filters.status || '');
    const [productId, setProductId] = useState(filters.product_id || '');

    const handleFilter = () => {
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (status) params.append('status', status);
        if (productId) params.append('product_id', productId);
        
        window.location.href = `/admin/reports/ticket-report?${params.toString()}`;
    };

    const handleExport = () => {
        const params = new URLSearchParams();
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        if (status) params.append('status', status);
        if (productId) params.append('product_id', productId);
        
        window.location.href = `/admin/reports/ticket-report/export?${params.toString()}`;
    };

    return (
        <>
            <Head title="Laporan Tiket" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="bg-white rounded-lg shadow-md p-6">
                        <h1 className="text-3xl font-bold mb-6 text-gray-800">Laporan Tiket</h1>

                        {/* Stats */}
                        <div className="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
                            <div className="bg-blue-50 p-4 rounded-lg">
                                <p className="text-sm text-gray-600">Total Penjualan</p>
                                <p className="text-2xl font-bold text-blue-600">{stats.total_sales}</p>
                            </div>
                            <div className="bg-green-50 p-4 rounded-lg">
                                <p className="text-sm text-gray-600">Selesai</p>
                                <p className="text-2xl font-bold text-green-600">{stats.completed}</p>
                            </div>
                            <div className="bg-yellow-50 p-4 rounded-lg">
                                <p className="text-sm text-gray-600">Pending</p>
                                <p className="text-2xl font-bold text-yellow-600">{stats.pending}</p>
                            </div>
                            <div className="bg-red-50 p-4 rounded-lg">
                                <p className="text-sm text-gray-600">Dibatalkan</p>
                                <p className="text-2xl font-bold text-red-600">{stats.cancelled}</p>
                            </div>
                            <div className="bg-purple-50 p-4 rounded-lg">
                                <p className="text-sm text-gray-600">Total Pendapatan</p>
                                <p className="text-2xl font-bold text-purple-600">Rp {(stats.total_revenue || 0).toLocaleString('id-ID')}</p>
                            </div>
                        </div>

                        {/* Filters */}
                        <div className="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 className="text-lg font-semibold mb-4">Filter</h3>
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                                    <input
                                        type="date"
                                        value={startDate}
                                        onChange={(e) => setStartDate(e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                                    <input
                                        type="date"
                                        value={endDate}
                                        onChange={(e) => setEndDate(e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select
                                        value={status}
                                        onChange={(e) => setStatus(e.target.value)}
                                        className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">Semua Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="completed">Selesai</option>
                                        <option value="cancelled">Dibatalkan</option>
                                    </select>
                                </div>
                                <div className="flex items-end gap-2">
                                    <button
                                        onClick={handleFilter}
                                        className="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                    >
                                        Filter
                                    </button>
                                </div>
                            </div>
                            <div className="mt-4">
                                <button
                                    onClick={handleExport}
                                    className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                >
                                    📥 Export CSV
                                </button>
                            </div>
                        </div>

                        {/* Table */}
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-200">
                                    <tr>
                                        <th className="px-4 py-2 text-left">Kode Penjualan</th>
                                        <th className="px-4 py-2 text-left">Nama Pembeli</th>
                                        <th className="px-4 py-2 text-center">Jumlah</th>
                                        <th className="px-4 py-2 text-right">Total</th>
                                        <th className="px-4 py-2 text-left">Status</th>
                                        <th className="px-4 py-2 text-left">Status Pembayaran</th>
                                        <th className="px-4 py-2 text-left">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {tickets?.data && tickets.data.length > 0 ? (
                                        tickets.data.map((ticket) => (
                                            <tr key={ticket.id} className="border-t hover:bg-gray-50">
                                                <td className="px-4 py-2 font-mono text-blue-600">{ticket.sale_code}</td>
                                                <td className="px-4 py-2">{ticket.user_name}</td>
                                                <td className="px-4 py-2 text-center">{ticket.total_qty}</td>
                                                <td className="px-4 py-2 text-right font-semibold">
                                                    Rp {ticket.total_amount?.toLocaleString('id-ID') || 0}
                                                </td>
                                                <td className="px-4 py-2">
                                                    <span className={`px-2 py-1 rounded text-xs font-semibold ${
                                                        ticket.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                        ticket.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-red-100 text-red-800'
                                                    }`}>
                                                        {ticket.status === 'completed' ? 'Selesai' : 
                                                         ticket.status === 'pending' ? 'Pending' : 'Dibatalkan'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-2">
                                                    <span className={`px-2 py-1 rounded text-xs font-semibold ${
                                                        ticket.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                                                        ticket.payment_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-red-100 text-red-800'
                                                    }`}>
                                                        {ticket.payment_status === 'paid' ? 'Lunas' : 
                                                         ticket.payment_status === 'pending' ? 'Menunggu' : 'Belum Bayar'}
                                                    </span>
                                                </td>
                                                <td className="px-4 py-2 text-sm">
                                                    {ticket.created_at ? format(new Date(ticket.created_at), 'dd MMM yyyy', { locale: id }) : '-'}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="7" className="px-4 py-6 text-center text-gray-500">
                                                Tidak ada data tiket
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {/* Pagination */}
                        {tickets?.links && (
                            <div className="mt-6 flex justify-between items-center">
                                <div className="text-sm text-gray-600">
                                    Menampilkan halaman dari total data
                                </div>
                                <div className="flex gap-2">
                                    {tickets.links.map((link, idx) => (
                                        <Link
                                            key={idx}
                                            href={link.url || '#'}
                                            className={`px-3 py-1 rounded ${
                                                link.active
                                                    ? 'bg-blue-600 text-white'
                                                    : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                            }`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
