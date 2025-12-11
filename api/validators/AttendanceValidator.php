<?php

require_once __DIR__ . '/../config/Config.php';

class AttendanceValidator
{
    public static function validateStatus($status)
    {
        if (empty($status)) {
            return [
                'valid' => false,
                'message' => 'Status is required'
            ];
        }

        if (!in_array($status, Config::VALID_STATUSES)) {
            return [
                'valid' => false,
                'message' => 'Status tidak valid. Gunakan: ' . implode(', ', Config::VALID_STATUSES)
            ];
        }

        return [
            'valid' => true,
            'message' => 'Status valid'
        ];
    }

    public static function validateAttendanceFlow($status, $lastStatus, $employeeName)
    {
        switch ($status) {
            case Config::STATUS_MASUK:
                if ($lastStatus === Config::STATUS_MASUK) {
                    return [
                        'valid' => false,
                        'message' => "$employeeName sudah absen masuk hari ini."
                    ];
                }
                break;

            case Config::STATUS_PULANG:
                if (!$lastStatus) {
                    return [
                        'valid' => false,
                        'message' => "$employeeName belum absen masuk hari ini."
                    ];
                }
                if (!in_array($lastStatus, [Config::STATUS_MASUK, Config::STATUS_LEMBUR])) {
                    return [
                        'valid' => false,
                        'message' => "$employeeName belum absen masuk atau lembur sebelum pulang."
                    ];
                }
                if ($lastStatus === Config::STATUS_PULANG) {
                    return [
                        'valid' => false,
                        'message' => "$employeeName sudah absen pulang hari ini."
                    ];
                }
                break;

            case Config::STATUS_LEMBUR:
                if (!$lastStatus || $lastStatus !== Config::STATUS_PULANG) {
                    return [
                        'valid' => false,
                        'message' => "$employeeName harus absen pulang terlebih dahulu sebelum lembur."
                    ];
                }
                break;

            case Config::STATUS_PULANG_LEMBUR:
                if (!$lastStatus || $lastStatus !== Config::STATUS_LEMBUR) {
                    return [
                        'valid' => false,
                        'message' => "$employeeName belum absen lembur hari ini."
                    ];
                }
                break;

            default:
                return [
                    'valid' => false,
                    'message' => 'Status absensi tidak valid.'
                ];
        }

        return [
            'valid' => true,
            'message' => 'Validasi flow berhasil'
        ];
    }
}
