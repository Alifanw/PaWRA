<?php

class Config
{
    // Database Configuration
    const DB_HOST = 'localhost';
    const DB_NAME = 'walini_pj';
    const DB_USER = 'walini_user';
    const DB_PASS = 'raHAS1@walini';

    // Security
    const API_TOKEN = 'SECURE_KEY_IGASAR';
    
    // Doorlock Configuration
    const DOORLOCK_API_URL = 'http://192.168.30.108:5000/door/open'; // Real Raspberry Pi
    const DOORLOCK_TOKEN = 'SECURE_KEY_IGASAR';
    const DOORLOCK_DEFAULT_DELAY = 3;
    
    // Rate Limiting
    const RATE_LIMIT_SECONDS = 5; // Prevent double tap within 5 seconds (DEMO MODE)
    
    // Logs
    const LOG_DIR = __DIR__ . '/../logs/';
    const ABSEN_LOG = self::LOG_DIR . 'absen.log';
    const DOORLOCK_LOG = self::LOG_DIR . 'doorlock.log';
    
    // Attendance Status
    const STATUS_MASUK = 'masuk';
    const STATUS_PULANG = 'pulang';
    const STATUS_LEMBUR = 'lembur';
    const STATUS_PULANG_LEMBUR = 'pulang_lembur';
    
    const VALID_STATUSES = [
        self::STATUS_MASUK,
        self::STATUS_PULANG,
        self::STATUS_LEMBUR,
        self::STATUS_PULANG_LEMBUR
    ];
}
