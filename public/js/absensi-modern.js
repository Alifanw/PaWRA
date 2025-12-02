/**
 * ABSENSI MODERN - JavaScript Client
 * Menggunakan API baru (/api/absen/*)
 * Evaluasi dari sistem lama: error handling lebih baik, UX lebih smooth
 */

// KONFIGURASI
const CONFIG = {
    API_BASE_URL: '/api/absen',
    API_TOKEN: 'SECURE_KEY_IGASAR',
    REFRESH_INTERVAL: 30000, // 30 detik
    AUTO_CLEAR_INPUT: 3000,  // 3 detik setelah sukses
};

// STATE MANAGEMENT
let currentEmployee = null;
let lastStatus = null;
let refreshTimer = null;

// ========================================
// UTILITY FUNCTIONS
// ========================================

function showLoading() {
    $('.loading-spinner').fadeIn(200);
}

function hideLoading() {
    $('.loading-spinner').fadeOut(200);
}

function setButtonsDisabled(disabled) {
    $('#btnMasuk, #btnPulang, #btnLembur, #btnPulangLembur').prop('disabled', disabled);
}

function clearInput() {
    $('#kodePegawai').val('').focus();
    currentEmployee = null;
    lastStatus = null;
    $('#employeeInfo').removeClass('show');
    setButtonsDisabled(true);
}

function updateClock() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('id-ID');
    $('#currentTime').text(timeStr);
}

// ========================================
// API FUNCTIONS
// ========================================

/**
 * Load dashboard summary (statistik hari ini)
 */
async function loadDashboard() {
    try {
        const response = await fetch(`${CONFIG.API_BASE_URL}/today?token=${CONFIG.API_TOKEN}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const summary = data.data.summary;
            $('#totalEmployees').text(summary.total_employees || 0);
            $('#totalMasuk').text(summary.total_masuk || 0);
            $('#totalPulang').text(summary.total_pulang || 0);
            
            // Update recent activities table
            updateActivityTable(data.data.recent_activities || []);
        } else {
            console.error('Dashboard load failed:', data.message);
        }
    } catch (error) {
        console.error('Dashboard API error:', error);
    }
}

/**
 * Update activity table
 */
function updateActivityTable(activities) {
    const tbody = $('#activityTable');
    tbody.empty();
    
    if (!activities || activities.length === 0) {
        tbody.html('<tr><td colspan="4" class="text-center text-muted">Belum ada aktivitas hari ini</td></tr>');
        return;
    }
    
    activities.forEach(act => {
        const badgeClass = getStatusBadgeClass(act.status);
        const row = `
            <tr>
                <td><small>${act.time || '-'}</small></td>
                <td><strong>${act.employee_name || '-'}</strong></td>
                <td><code>${act.employee_code || '-'}</code></td>
                <td><span class="badge ${badgeClass}">${act.status || '-'}</span></td>
            </tr>
        `;
        tbody.append(row);
    });
}

function getStatusBadgeClass(status) {
    const statusMap = {
        'masuk': 'bg-success badge-status',
        'pulang': 'bg-danger badge-status',
        'lembur': 'bg-warning badge-status',
        'pulang_lembur': 'bg-info badge-status'
    };
    return statusMap[status] || 'bg-secondary badge-status';
}

/**
 * Check employee status
 */
async function checkEmployeeStatus(kode) {
    try {
        showLoading();
        const response = await fetch(`${CONFIG.API_BASE_URL}/check/${kode}?token=${CONFIG.API_TOKEN}`);
        const data = await response.json();
        
        hideLoading();
        
        if (data.status === 'success') {
            currentEmployee = data.data;
            lastStatus = currentEmployee.last_action_today;
            
            // Show employee info
            $('#employeeName').text(currentEmployee.employee_name);
            $('#employeeCode').text(currentEmployee.employee_code);
            
            if (lastStatus) {
                $('#lastAction').text(`Aktivitas terakhir: ${lastStatus} (${currentEmployee.last_action_time})`);
            } else {
                $('#lastAction').text('Belum ada aktivitas hari ini');
            }
            
            $('#employeeInfo').addClass('show');
            
            // Enable appropriate buttons based on last status
            enableAppropriateButtons(lastStatus);
            
            // Show action guidance
            showActionGuidance(lastStatus);
            
            return true;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Kode Tidak Ditemukan',
                text: data.message || 'Kode karyawan tidak terdaftar',
                timer: 2000
            });
            clearInput();
            return false;
        }
    } catch (error) {
        hideLoading();
        console.error('Check employee error:', error);
        Swal.fire('Error', 'Gagal memeriksa status karyawan', 'error');
        return false;
    }
}

/**
 * Enable buttons based on last status
 */
function enableAppropriateButtons(lastStatus) {
    // Reset all buttons
    setButtonsDisabled(true);
    
    if (!lastStatus) {
        // Belum ada aktivitas hari ini -> hanya bisa MASUK
        $('#btnMasuk').prop('disabled', false);
    } else if (lastStatus === 'masuk') {
        // Sudah masuk -> bisa PULANG atau LEMBUR
        $('#btnPulang').prop('disabled', false);
        $('#btnLembur').prop('disabled', false);
    } else if (lastStatus === 'pulang') {
        // Sudah pulang -> bisa LEMBUR
        $('#btnLembur').prop('disabled', false);
    } else if (lastStatus === 'lembur') {
        // Sudah lembur -> bisa PULANG LEMBUR
        $('#btnPulangLembur').prop('disabled', false);
    } else if (lastStatus === 'pulang_lembur') {
        // Sudah pulang lembur -> tidak ada aksi lagi
        // Semua tombol tetap disabled
    }
}

/**
 * Show action guidance
 */
function showActionGuidance(lastStatus) {
    const actionStatus = $('#actionStatus');
    actionStatus.removeClass('show alert-info alert-success alert-warning alert-danger');
    
    let message = '';
    let alertClass = 'alert-info';
    
    if (!lastStatus) {
        message = '<i class="fas fa-info-circle me-2"></i>Silakan klik tombol <strong>MASUK</strong> untuk absen masuk';
        alertClass = 'alert-success';
    } else if (lastStatus === 'masuk') {
        message = '<i class="fas fa-info-circle me-2"></i>Anda bisa <strong>PULANG</strong> atau lanjut <strong>LEMBUR</strong>';
        alertClass = 'alert-info';
    } else if (lastStatus === 'pulang') {
        message = '<i class="fas fa-info-circle me-2"></i>Anda sudah pulang. Bisa mulai <strong>LEMBUR</strong> jika perlu';
        alertClass = 'alert-warning';
    } else if (lastStatus === 'lembur') {
        message = '<i class="fas fa-info-circle me-2"></i>Silakan <strong>PULANG LEMBUR</strong> setelah selesai';
        alertClass = 'alert-info';
    } else if (lastStatus === 'pulang_lembur') {
        message = '<i class="fas fa-check-circle me-2"></i>Aktivitas hari ini sudah selesai';
        alertClass = 'alert-success';
    }
    
    actionStatus.html(message).addClass(`show ${alertClass}`);
}

/**
 * Submit attendance
 */
async function submitAttendance(status) {
    if (!currentEmployee) {
        Swal.fire('Perhatian', 'Silakan scan kode karyawan terlebih dahulu', 'warning');
        return;
    }
    
    // Konfirmasi
    const confirmResult = await Swal.fire({
        title: 'Konfirmasi Absensi',
        html: `
            <div class="text-start">
                <p><strong>Nama:</strong> ${currentEmployee.employee_name}</p>
                <p><strong>Kode:</strong> ${currentEmployee.employee_code}</p>
                <p><strong>Aksi:</strong> <span class="badge bg-primary">${status.toUpperCase()}</span></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Lanjutkan',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#667eea'
    });
    
    if (!confirmResult.isConfirmed) {
        return;
    }
    
    try {
        showLoading();
        setButtonsDisabled(true);
        
        const response = await fetch(CONFIG.API_BASE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                token: CONFIG.API_TOKEN,
                kode: currentEmployee.employee_code,
                status: status,
                device_code: 'WEB-KIOSK-01'
            })
        });
        
        const data = await response.json();
        hideLoading();
        
        if (data.status === 'success') {
            // Success notification
            await Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: `
                    <div class="text-center">
                        <h5>${currentEmployee.employee_name}</h5>
                        <p class="mb-1">Absensi <strong>${status.toUpperCase()}</strong> berhasil</p>
                        <p class="text-muted mb-0"><small>${data.data.timestamp}</small></p>
                        ${data.data.door_triggered ? '<p class="text-success mt-2"><i class="fas fa-door-open"></i> Pintu terbuka</p>' : ''}
                    </div>
                `,
                timer: 3000,
                showConfirmButton: false
            });
            
            // Reload dashboard
            await loadDashboard();
            
            // Clear input after delay
            setTimeout(() => {
                clearInput();
            }, CONFIG.AUTO_CLEAR_INPUT);
            
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Gagal menyimpan absensi',
            });
            setButtonsDisabled(false);
        }
        
    } catch (error) {
        hideLoading();
        console.error('Submit attendance error:', error);
        Swal.fire('Error', 'Terjadi kesalahan saat mengirim data', 'error');
        setButtonsDisabled(false);
    }
}

// ========================================
// EVENT HANDLERS
// ========================================


$(function() {
    console.log('Absensi Modern System Initialized');

    // Initial load
    loadDashboard();
    updateClock();

    // Auto refresh dashboard
    refreshTimer = setInterval(() => {
        loadDashboard();
        updateClock();
    }, CONFIG.REFRESH_INTERVAL);

    // Clock update every second
    setInterval(updateClock, 1000);

    // Input kode pegawai - auto check on Enter
    $('#kodePegawai').attr({
        'aria-label': 'Input Kode Pegawai',
        'autocomplete': 'off',
        'autofocus': true
    });
    $('#kodePegawai').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            const kode = $(this).val().trim();
            if (kode) {
                checkEmployeeStatus(kode);
            }
        }
    });

    // Keyboard navigation for buttons
    $('.btn-absen').attr('tabindex', 0).on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            $(this).trigger('click');
        }
    });

    // Auto check on blur (untuk barcode scanner)
    $('#kodePegawai').on('blur', function() {
        const kode = $(this).val().trim();
        if (kode && !currentEmployee) {
            setTimeout(() => {
                checkEmployeeStatus(kode);
            }, 300);
        }
    });

    // Button event handlers
    $('#btnMasuk').on('click', () => submitAttendance('masuk'));
    $('#btnPulang').on('click', () => submitAttendance('pulang'));
    $('#btnLembur').on('click', () => submitAttendance('lembur'));
    $('#btnPulangLembur').on('click', () => submitAttendance('pulang_lembur'));

    // Refresh button
    $('#btnRefresh').on('click', function() {
        $(this).find('i').addClass('fa-spin');
        loadDashboard().finally(() => {
            $(this).find('i').removeClass('fa-spin');
        });
    });

    // Focus management
    $('#kodePegawai').focus();

    // Announce status changes for screen readers
    const srAnnounce = $('<div>', { id: 'sr-announce', 'aria-live': 'polite', 'class': 'visually-hidden', 'style': 'position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden;' });
    $('body').append(srAnnounce);
    function announce(msg) { srAnnounce.text(msg); }
    $(document).on('statusChange', function(e, msg) { announce(msg); });

    // Prevent form resubmission on page reload
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
});

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
    }
});
