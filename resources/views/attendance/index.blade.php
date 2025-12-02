<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Absensi Harian | Air Panas Walini</title>
    
    <!-- Tailwind CSS (already included in app layout) -->
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Use global theme.css for all design tokens and transitions -->
    <!-- Dark Mode Toggle -->
    <button id="darkModeToggle" aria-label="Toggle dark mode" class="fixed top-6 right-6 z-50 bg-card-bg border-none rounded-full w-12 h-12 shadow-card flex items-center justify-center cursor-pointer transition-theme">
        <span id="darkModeIcon" class="text-2xl">🌙</span>
    </button>
</head>
<body class="bg-primary min-h-screen transition-theme">

<div class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="modern-card modern-header mb-8 bg-card-bg shadow-card border border-border-color transition-theme">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="flex items-center space-x-4 mb-4 md:mb-0">
                    <div class="bg-primary p-4 rounded-xl transition-theme">
                        <i class="fas fa-user-check text-white text-3xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold">Absensi Harian</h1>
                        <p class="opacity-80 mt-1">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</p>
                    </div>
                </div>
                <button onclick="location.reload()" class="bg-primary text-white px-6 py-3 rounded-xl transition-theme shadow-card hover:shadow-lg">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>
    <script>
    // Dark mode toggle logic
    const darkToggle = document.getElementById('darkModeToggle');
    const darkIcon = document.getElementById('darkModeIcon');
    function setDarkMode(on) {
        if(on) {
            document.body.classList.add('dark');
            darkIcon.textContent = '☀️';
            localStorage.setItem('absensiDarkMode', '1');
        } else {
            document.body.classList.remove('dark');
            darkIcon.textContent = '🌙';
            localStorage.setItem('absensiDarkMode', '0');
        }
    }
    darkToggle.onclick = () => setDarkMode(!document.body.classList.contains('dark'));
    // On load
    if(localStorage.getItem('absensiDarkMode') === '1') setDarkMode(true);
    </script>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Pegawai -->
        <div class="bg-card-bg rounded-2xl shadow-card p-6 transform hover:scale-105 transition-theme">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Total Pegawai</p>
                    <p class="text-4xl font-bold text-blue-600 mt-2">{{ $totalEmployees }}</p>
                </div>
                <div class="bg-blue-100 p-4 rounded-xl">
                    <i class="fas fa-users text-blue-600 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Hadir Hari Ini -->
        <div class="bg-card-bg rounded-2xl shadow-card p-6 transform hover:scale-105 transition-theme">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Hadir Hari Ini</p>
                    <p class="text-4xl font-bold text-green-600 mt-2">{{ $attendedToday }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-xl">
                    <i class="fas fa-user-check text-green-600 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Belum Absen -->
        <div class="bg-card-bg rounded-2xl shadow-card p-6 transform hover:scale-105 transition-theme">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase">Belum Absen</p>
                    <p class="text-4xl font-bold text-amber-600 mt-2">{{ $notAttendedYet }}</p>
                </div>
                <div class="bg-amber-100 p-4 rounded-xl">
                    <i class="fas fa-user-times text-amber-600 text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Absensi -->
    <div class="bg-card-bg rounded-2xl shadow-card p-8 mb-8 transition-theme">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center bg-primary text-white px-6 py-3 rounded-xl shadow-card mb-4 transition-theme">
                <i class="fas fa-fingerprint text-2xl mr-3"></i>
                <h2 class="text-2xl font-bold">Form Absensi</h2>
            </div>
        </div>

        <!-- Input Kode -->
        <div class="max-w-md mx-auto mb-8">
            <input 
                type="text" 
                id="employeeCode" 
                placeholder="Masukkan Kode Pegawai" 
                class="w-full px-6 py-4 text-2xl text-center font-semibold border-4 border-primary rounded-2xl focus:outline-none focus:ring-4 focus:ring-primary focus:border-primary transition-theme pulse-green"
                autofocus
            >
        </div>

        <!-- Tombol Status -->
        <div class="flex flex-wrap justify-center gap-4">
            <button onclick="submitAttendance('Masuk')" class="btn-status bg-primary text-white px-8 py-4 rounded-xl font-bold text-lg shadow-card hover:shadow-lg transform hover:scale-105 transition-theme">
                <i class="fas fa-door-open mr-2"></i>Masuk
            </button>
            <button onclick="submitAttendance('Pulang')" class="btn-status bg-primary text-white px-8 py-4 rounded-xl font-bold text-lg shadow-card hover:shadow-lg transform hover:scale-105 transition-theme">
                <i class="fas fa-door-closed mr-2"></i>Pulang
            </button>
            <button onclick="submitAttendance('Lembur')" class="btn-status bg-primary text-white px-8 py-4 rounded-xl font-bold text-lg shadow-card hover:shadow-lg transform hover:scale-105 transition-theme">
                <i class="fas fa-clock mr-2"></i>Lembur
            </button>
            <button onclick="submitAttendance('Pulang Lembur')" class="btn-status bg-primary text-white px-8 py-4 rounded-xl font-bold text-lg shadow-card hover:shadow-lg transform hover:scale-105 transition-theme">
                <i class="fas fa-home mr-2"></i>Pulang Lembur
            </button>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="bg-card-bg rounded-2xl shadow-card p-8 transition-theme">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-primary">
                <i class="fas fa-list text-blue-600 mr-3"></i>Riwayat Absensi Hari Ini
            </h3>
            <span class="bg-secondary text-primary px-4 py-2 rounded-xl font-semibold transition-theme">
                {{ $recentLogs->count() }} Record
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full transition-theme">
                <thead>
                    <tr class="bg-secondary border-b-2 border-border-color transition-theme">
                        <th class="px-6 py-4 text-left text-sm font-bold text-primary uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-primary uppercase">Nama</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-primary uppercase">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-primary uppercase">Waktu</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-primary uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-color transition-theme">
                    @forelse($recentLogs as $log)
                    <tr class="hover:bg-secondary transition-theme">
                        <td class="px-6 py-4 text-primary">{{ $log->id }}</td>
                        <td class="px-6 py-4 font-semibold text-primary">{{ $log->employee->name }}</td>
                        <td class="px-6 py-4 text-secondary font-mono">{{ $log->employee->code }}</td>
                        <td class="px-6 py-4 text-secondary">{{ $log->event_time->format('H:i:s') }}</td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $badges = [
                                    'masuk' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Masuk'],
                                    'pulang' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Pulang'],
                                    'lembur' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'label' => 'Lembur'],
                                    'pulang_lembur' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'label' => 'Pulang Lembur'],
                                ];
                                $badge = $badges[$log->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst($log->status)];
                            @endphp
                            <span class="bg-secondary text-primary px-4 py-2 rounded-lg font-semibold text-sm transition-theme">
                                {{ $badge['label'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-secondary">
                            <i class="fas fa-inbox text-5xl mb-4 text-secondary"></i>
                            <p class="text-lg">Belum ada data absensi hari ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    // Auto-focus input saat load
    document.getElementById('employeeCode').focus();

    // Submit absensi
    function submitAttendance(status) {
        const code = document.getElementById('employeeCode').value.trim();
        
        if (!code) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Masukkan kode pegawai terlebih dahulu!',
                confirmButtonColor: '#3b82f6'
            });
            document.getElementById('employeeCode').focus();
            return;
        }

        // Disable semua tombol
        document.querySelectorAll('.btn-status').forEach(btn => btn.disabled = true);

        // Kirim request
        fetch('{{ route('admin.attendance.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code: code,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    html: `<div class="text-lg">
                        <p class="font-bold text-xl mb-2">${data.data.employee_name}</p>
                        <p>${data.message}</p>
                        ${data.data.door_opened ? '<p class="text-green-600 mt-2"><i class="fas fa-door-open"></i> Pintu telah dibuka</p>' : ''}
                    </div>`,
                    timer: 2000,
                    showConfirmButton: false,
                    timerProgressBar: true
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonColor: '#ef4444'
                });
                document.querySelectorAll('.btn-status').forEach(btn => btn.disabled = false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: 'Terjadi kesalahan koneksi ke server',
                confirmButtonColor: '#ef4444'
            });
            document.querySelectorAll('.btn-status').forEach(btn => btn.disabled = false);
        });
    }

    // Enter key untuk submit
    document.getElementById('employeeCode').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            submitAttendance('Masuk'); // Default: Masuk
        }
    });

    // Auto-reload setiap 5 menit untuk update tabel
    setTimeout(() => location.reload(), 300000);
</script>

</body>
</html>
