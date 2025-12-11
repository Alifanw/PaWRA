<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceLog;
use App\Services\DoorlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected DoorlockService $doorlockService;

    public function __construct(DoorlockService $doorlockService)
    {
        $this->doorlockService = $doorlockService;
    }

    /**
     * Tampilkan halaman absensi harian (dashboard kiosk)
     */
    public function index()
    {
        $today = Carbon::today();

        // Statistik hari ini
        $totalEmployees = Employee::where('is_active', true)->count();
        
        $attendedToday = AttendanceLog::whereDate('event_time', $today)
            ->distinct('employee_id')
            ->count('employee_id');
        
        $notAttendedYet = $totalEmployees - $attendedToday;

        // Riwayat absensi terbaru (hari ini)
        $recentLogs = AttendanceLog::with('employee')
            ->whereDate('event_time', $today)
            ->orderBy('event_time', 'desc')
            ->limit(50)
            ->get();

        return inertia('Admin/Attendance/Index', [
            'totalEmployees' => $totalEmployees,
            'attendedToday' => $attendedToday,
            'notAttendedYet' => $notAttendedYet,
            'recentLogs' => $recentLogs
        ]);
    }

    /**
     * Proses absensi (via AJAX dari kiosk)
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'status' => 'required|in:Masuk,Pulang,Lembur,Pulang Lembur'
        ]);

        $employeeCode = $request->input('code');
        $statusInput = $request->input('status'); // GUI format: "Masuk", "Pulang", etc.

        // Convert status ke database ENUM format
        $statusMapping = [
            'Masuk' => 'masuk',
            'Pulang' => 'pulang',
            'Lembur' => 'lembur',
            'Pulang Lembur' => 'pulang_lembur'
        ];
        $status = $statusMapping[$statusInput];

        DB::beginTransaction();

        try {
            // 1. Validasi employee
            $employee = Employee::where('code', $employeeCode)->first();

            if (!$employee) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Kode karyawan {$employeeCode} tidak ditemukan"
                ], 404);
            }

            if (!$employee->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => "{$employee->name} sudah tidak aktif"
                ], 403);
            }

            // 2. Cek log terakhir hari ini
            $today = Carbon::today();
            $lastLog = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('event_time', $today)
                ->orderBy('event_time', 'desc')
                ->first();

            // 3. Validasi logika absensi
            $validationResult = $this->validateAttendanceLogic($employee, $status, $lastLog);
            if (!$validationResult['valid']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validationResult['message']
                ], 400);
            }

            // 4. Simpan log absensi
            $attendanceLog = AttendanceLog::create([
                'employee_id' => $employee->id,
                'status' => $status,
                'event_time' => Carbon::now()
            ]);

            Log::info('Attendance recorded', [
                'employee_code' => $employeeCode,
                'employee_name' => $employee->name,
                'status' => $status,
                'event_time' => $attendanceLog->event_time
            ]);

            // 5. Trigger doorlock (auto unlock untuk semua status)
            $doorResult = $this->doorlockService->triggerForAttendance(
                $employeeCode,
                $status,
                5 // 5 detik delay
            );

            if (!$doorResult['success']) {
                Log::warning('Doorlock trigger failed but attendance saved', [
                    'employee_code' => $employeeCode,
                    'status' => $status,
                    'door_error' => $doorResult['message']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil! {$employee->name} absen {$statusInput}.",
                'data' => [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'employee_code' => $employeeCode,
                    'status' => $status,
                    'event_time' => $attendanceLog->event_time->format('Y-m-d H:i:s'),
                    'door_opened' => $doorResult['triggered'] ?? false
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Attendance save failed', [
                'employee_code' => $employeeCode,
                'status' => $status,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validasi logika absensi berdasarkan status terakhir
     */
    private function validateAttendanceLogic(Employee $employee, string $status, ?AttendanceLog $lastLog): array
    {
        $lastStatus = $lastLog?->status;

        switch ($status) {
            case 'masuk':
                // Tidak boleh masuk 2x dalam sehari
                if ($lastStatus === 'masuk') {
                    return [
                        'valid' => false,
                        'message' => "{$employee->name} sudah absen masuk hari ini"
                    ];
                }
                // Tidak boleh masuk lagi jika sudah pulang
                if (in_array($lastStatus, ['pulang', 'pulang_lembur'])) {
                    return [
                        'valid' => false,
                        'message' => "{$employee->name} sudah pulang hari ini"
                    ];
                }
                break;

            case 'pulang':
                // Harus sudah masuk atau lembur dulu
                if (!in_array($lastStatus, ['masuk', 'lembur'])) {
                    return [
                        'valid' => false,
                        'message' => "{$employee->name} belum absen masuk hari ini"
                    ];
                }
                break;

            case 'lembur':
                // Harus sudah pulang dulu
                if ($lastStatus !== 'pulang') {
                    return [
                        'valid' => false,
                        'message' => "{$employee->name} harus absen pulang terlebih dahulu"
                    ];
                }
                break;

            case 'pulang_lembur':
                // Harus sudah lembur dulu
                if ($lastStatus !== 'lembur') {
                    return [
                        'valid' => false,
                        'message' => "{$employee->name} belum absen lembur hari ini"
                    ];
                }
                break;
        }

        return ['valid' => true];
    }

    /**
     * Get attendance logs (untuk DataTables AJAX)
     */
    public function getLogs(Request $request)
    {
        $query = AttendanceLog::with('employee')
            ->select('attendance_logs.*');

        // Filter by date
        if ($request->has('date')) {
            $date = Carbon::parse($request->input('date'));
            $query->whereDate('event_time', $date);
        }

        // Filter by employee
        if ($request->has('employee_code')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('code', $request->input('employee_code'));
            });
        }

        $logs = $query->orderBy('event_time', 'desc')->get();

        return response()->json([
            'data' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'employee_name' => $log->employee->name ?? 'N/A',
                    'employee_code' => $log->employee->code ?? 'N/A',
                    'status' => $log->status,
                    'event_time' => $log->event_time->format('Y-m-d H:i:s'),
                    'status_badge' => $this->getStatusBadge($log->status)
                ];
            })
        ]);
    }

    /**
     * Get badge class untuk status
     */
    private function getStatusBadge(string $status): string
    {
        return match ($status) {
            'masuk' => 'badge-success',
            'pulang' => 'badge-danger',
            'lembur' => 'badge-warning',
            'pulang_lembur' => 'badge-info',
            default => 'badge-secondary'
        };
    }

    /**
     * Laporan absensi per periode
     */
    public function report(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth());

        $logs = AttendanceLog::with('employee')
            ->whereBetween('event_time', [$startDate, $endDate])
            ->orderBy('event_time', 'desc')
            ->get();

        // Return an Inertia page so SPA navigations return a proper Inertia
        // response (component name + props). Previously this returned a plain
        // Blade view which caused the client to receive a non-Inertia HTML
        // response and the Inertia page "component" became undefined.
        return inertia('Admin/Attendance/Report', compact('logs', 'startDate', 'endDate'));
    }

    /**
     * Test doorlock connection (admin only)
     */
    public function testDoorlock()
    {
        $result = $this->doorlockService->openDoorManual(3);

        return response()->json($result);
    }

    /**
     * Check doorlock status (admin only)
     */
    public function doorlockStatus()
    {
        $result = $this->doorlockService->getStatus();

        return response()->json($result);
    }

    /**
     * Health check doorlock Pi
     */
    public function doorlockHealth()
    {
        $result = $this->doorlockService->healthCheck();

        return response()->json($result);
    }
}
