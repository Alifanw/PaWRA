<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // rfid_logs: ensure FK to employees exists and is RESTRICT
        if (Schema::hasTable('rfid_logs') && Schema::hasColumn('rfid_logs', 'employee_id') && Schema::hasTable('employees')) {
            $driver = Schema::getConnection()->getDriverName();
            $exists = false;
            if ($driver === 'mysql') {
                $exists = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', DB::getDatabaseName())
                    ->where('TABLE_NAME', 'rfid_logs')
                    ->where('COLUMN_NAME', 'employee_id')
                    ->where('CONSTRAINT_NAME', 'rfid_employee_fk')
                    ->exists();
            } elseif ($driver === 'sqlite') {
                $fks = DB::select("PRAGMA foreign_key_list('rfid_logs')");
                foreach ($fks as $fk) {
                    if ((isset($fk->from) && $fk->from === 'employee_id') || (is_array((array) $fk) && in_array('employee_id', (array) $fk))) {
                        $exists = true;
                        break;
                    }
                }
            }

            if (!$exists) {
                Schema::table('rfid_logs', function (Blueprint $table) {
                    $table->foreign('employee_id', 'rfid_employee_fk')
                        ->references('id')->on('employees')
                        ->onDelete('restrict');
                });
            }
        }

        // attendance_logs: ensure FK to employees exists with RESTRICT
        if (Schema::hasTable('attendance_logs') && Schema::hasColumn('attendance_logs', 'employee_id') && Schema::hasTable('employees')) {
            $exists = false;
            if ($driver === 'mysql') {
                $exists = DB::table('information_schema.KEY_COLUMN_USAGE')
                    ->where('TABLE_SCHEMA', DB::getDatabaseName())
                    ->where('TABLE_NAME', 'attendance_logs')
                    ->where('COLUMN_NAME', 'employee_id')
                    ->exists();
            } elseif ($driver === 'sqlite') {
                $fks = DB::select("PRAGMA foreign_key_list('attendance_logs')");
                foreach ($fks as $fk) {
                    if ((isset($fk->from) && $fk->from === 'employee_id') || (is_array((array) $fk) && in_array('employee_id', (array) $fk))) {
                        $exists = true;
                        break;
                    }
                }
            }

            if (!$exists) {
                Schema::table('attendance_logs', function (Blueprint $table) {
                    $table->foreign('employee_id')
                        ->references('id')->on('employees')
                        ->onDelete('restrict');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('rfid_logs')) {
            Schema::table('rfid_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign('rfid_employee_fk');
                } catch (\Exception $e) {
                }
            });
        }

        if (Schema::hasTable('attendance_logs')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                try {
                    $table->dropForeign(['employee_id']);
                } catch (\Exception $e) {
                }
            });
        }
    }
};
