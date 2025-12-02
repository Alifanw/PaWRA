import { useState, useEffect, useRef } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { 
    ClipboardDocumentCheckIcon, 
    UsersIcon, 
    UserMinusIcon,
    ArrowPathIcon,
    FingerPrintIcon,
    ArrowRightOnRectangleIcon,
    ArrowLeftOnRectangleIcon,
    ClockIcon,
    HomeIcon
} from '@heroicons/react/24/outline';
import { router } from '@inertiajs/react';
import Swal from 'sweetalert2';

export default function Attendance({ auth, totalEmployees, attendedToday, notAttendedYet, recentLogs }) {
    const [employeeCode, setEmployeeCode] = useState('');
    const [isSubmitting, setIsSubmitting] = useState(false);
    const inputRef = useRef(null);

    useEffect(() => {
        // Auto-focus input saat load
        inputRef.current?.focus();
    }, []);

    const statCards = [
        {
            name: 'Total Pegawai',
            value: totalEmployees,
            icon: UsersIcon,
            color: 'blue',
        },
        {
            name: 'Hadir Hari Ini',
            value: attendedToday,
            icon: ClipboardDocumentCheckIcon,
            color: 'green',
        },
        {
            name: 'Belum Absen',
            value: notAttendedYet,
            icon: UserMinusIcon,
            color: 'amber',
        },
    ];

    const statusButtons = [
        {
            label: 'Masuk',
            status: 'Masuk',
            icon: ArrowRightOnRectangleIcon,
            color: 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700',
        },
        {
            label: 'Pulang',
            status: 'Pulang',
            icon: ArrowLeftOnRectangleIcon,
            color: 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700',
        },
        {
            label: 'Lembur',
            status: 'Lembur',
            icon: ClockIcon,
            color: 'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700',
        },
        {
            label: 'Pulang Lembur',
            status: 'Pulang Lembur',
            icon: HomeIcon,
            color: 'bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-600 hover:to-cyan-700',
        },
    ];

    const submitAttendance = async (status) => {
        if (!employeeCode.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan kode pegawai terlebih dahulu!',
                confirmButtonColor: '#3b82f6'
            });
            inputRef.current?.focus();
            return;
        }

        setIsSubmitting(true);

        try {
            const response = await fetch(route('admin.attendance.store'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    code: employeeCode,
                    status: status
                })
            });

            const data = await response.json();

            if (data.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `
                        <div class="text-lg">
                            <p class="font-bold text-xl mb-2">${data.data.employee_name}</p>
                            <p>${data.message}</p>
                            ${data.data.door_opened ? '<p class="text-green-600 mt-2"><i class="fas fa-door-open"></i> Pintu telah dibuka</p>' : ''}
                        </div>
                    `,
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                });

                // Reload page untuk update data
                router.reload({ only: ['recentLogs', 'attendedToday', 'notAttendedYet'] });
                setEmployeeCode('');
                inputRef.current?.focus();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonColor: '#ef4444'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: 'Terjadi kesalahan koneksi ke server',
                confirmButtonColor: '#ef4444'
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    const getStatusBadge = (status) => {
        const badges = {
            'masuk': { bg: 'bg-green-100 dark:bg-green-900', text: 'text-green-800 dark:text-green-200', label: 'Masuk' },
            'pulang': { bg: 'bg-red-100 dark:bg-red-900', text: 'text-red-800 dark:text-red-200', label: 'Pulang' },
            'lembur': { bg: 'bg-amber-100 dark:bg-amber-900', text: 'text-amber-800 dark:text-amber-200', label: 'Lembur' },
            'pulang_lembur': { bg: 'bg-cyan-100 dark:bg-cyan-900', text: 'text-cyan-800 dark:text-cyan-200', label: 'Pulang Lembur' },
        };
        return badges[status] || { bg: 'bg-slate-100 dark:bg-slate-700', text: 'text-slate-800 dark:text-slate-200', label: status };
    };

    const handleKeyPress = (e) => {
        if (e.key === 'Enter' && !isSubmitting) {
            submitAttendance('Masuk'); // Default: Masuk saat tekan Enter
        }
    };

    return (
        <AdminLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <div className="flex items-center space-x-4">
                        <div className="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-xl">
                            <ClipboardDocumentCheckIcon className="w-8 h-8 text-white" />
                        </div>
                        <div>
                            <h2 className="text-2xl font-bold text-slate-800 dark:text-slate-100">Absensi Harian</h2>
                            <p className="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                {new Date().toLocaleDateString('id-ID', { 
                                    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}
                            </p>
                        </div>
                    </div>
                    <button
                        onClick={() => router.reload()}
                        className="flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
                    >
                        <ArrowPathIcon className="w-5 h-5 mr-2" />
                        Refresh
                    </button>
                </div>
            }
        >
            {/* Statistik Cards */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                {statCards.map((card, index) => {
                    const Icon = card.icon;
                    const colorClasses = {
                        blue: 'bg-blue-100 text-blue-600',
                        green: 'bg-green-100 text-green-600',
                        amber: 'bg-amber-100 text-amber-600',
                    };

                    return (
                        <div
                            key={index}
                            className="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-6 transform hover:scale-105 transition-transform duration-200"
                        >
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        {card.name}
                                    </p>
                                    <p className="text-4xl font-bold text-slate-900 dark:text-slate-100 mt-2">
                                        {card.value}
                                    </p>
                                </div>
                                <div className={`p-4 rounded-xl ${colorClasses[card.color]}`}>
                                    <Icon className="w-8 h-8" />
                                </div>
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Form Absensi */}
            <div className="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-8 mb-8">
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center bg-gradient-to-br from-green-400 to-green-600 text-white px-6 py-3 rounded-xl shadow-lg mb-4">
                        <FingerPrintIcon className="w-8 h-8 mr-3" />
                        <h2 className="text-2xl font-bold">Form Absensi</h2>
                    </div>
                </div>

                {/* Input Kode */}
                <div className="max-w-md mx-auto mb-8">
                    <input
                        ref={inputRef}
                        type="text"
                        value={employeeCode}
                        onChange={(e) => setEmployeeCode(e.target.value)}
                        onKeyPress={handleKeyPress}
                        placeholder="Masukkan Kode Pegawai"
                        disabled={isSubmitting}
                        className="w-full px-6 py-4 text-2xl text-center font-semibold border-4 border-green-500 rounded-2xl focus:outline-none focus:ring-4 focus:ring-green-300 focus:border-green-600 transition-all duration-200 disabled:opacity-50 dark:bg-slate-700 dark:text-slate-100"
                    />
                </div>

                {/* Tombol Status */}
                <div className="flex flex-wrap justify-center gap-4">
                    {statusButtons.map((button) => {
                        const Icon = button.icon;
                        return (
                            <button
                                key={button.status}
                                onClick={() => submitAttendance(button.status)}
                                disabled={isSubmitting}
                                className={`${button.color} text-white px-8 py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center`}
                            >
                                <Icon className="w-6 h-6 mr-2" />
                                {button.label}
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* Tabel Riwayat */}
            <div className="bg-white dark:bg-slate-800 rounded-xl shadow-sm p-8">
                <div className="flex items-center justify-between mb-6">
                    <h3 className="text-2xl font-bold text-slate-800 dark:text-slate-100 flex items-center">
                        <svg className="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Riwayat Absensi Hari Ini
                    </h3>
                    <span className="bg-blue-100 dark:bg-blue-900 px-4 py-2 rounded-xl font-semibold text-slate-900 dark:text-slate-100">
                        {recentLogs?.length || 0} Record
                    </span>
                </div>

                <div className="overflow-x-auto">
                    <table className="w-full">
                        <thead>
                            <tr className="bg-gradient-to-r from-blue-50 dark:from-slate-700 to-indigo-50 dark:to-slate-600 border-b-2 border-blue-200 dark:border-slate-600">
                                <th className="px-6 py-4 text-left text-sm font-bold text-slate-700 dark:text-slate-200 uppercase">ID</th>
                                <th className="px-6 py-4 text-left text-sm font-bold text-slate-700 dark:text-slate-200 uppercase">Nama</th>
                                <th className="px-6 py-4 text-left text-sm font-bold text-slate-700 dark:text-slate-200 uppercase">Kode</th>
                                <th className="px-6 py-4 text-left text-sm font-bold text-slate-700 dark:text-slate-200 uppercase">Waktu</th>
                                <th className="px-6 py-4 text-center text-sm font-bold text-slate-700 dark:text-slate-200 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                            {recentLogs && recentLogs.length > 0 ? (
                                recentLogs.map((log) => {
                                    const badge = getStatusBadge(log.status);
                                    return (
                                        <tr key={log.id} className="hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-150">
                                            <td className="px-6 py-4 text-slate-800 dark:text-slate-100">{log.id}</td>
                                            <td className="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">
                                                {log.employee?.name || 'N/A'}
                                            </td>
                                            <td className="px-6 py-4 text-slate-700 dark:text-slate-300 font-mono">
                                                {log.employee?.code || 'N/A'}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600 dark:text-slate-400">
                                                {new Date(log.event_time).toLocaleTimeString('id-ID')}
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                <span className={`${badge.bg} ${badge.text} px-4 py-2 rounded-lg font-semibold text-sm inline-block`}>
                                                    {badge.label}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="5" className="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                                        <svg className="mx-auto w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p className="text-lg">Belum ada data absensi hari ini</p>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
