<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rfid_logs', function (Blueprint $table) {
            // Add new columns if they don't exist yet
            if (!Schema::hasColumn('rfid_logs', 'rfid_code')) {
                $table->string('rfid_code', 50)->after('id');
            }

            if (!Schema::hasColumn('rfid_logs', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('rfid_code');
            }

            if (!Schema::hasColumn('rfid_logs', 'device_code')) {
                $table->string('device_code', 50)->nullable()->after('employee_id');
            }

            if (!Schema::hasColumn('rfid_logs', 'event_type')) {
                $table->enum('event_type', ['scan','granted','denied'])->default('scan')->after('device_code');
            }

            // Index and foreign key
            $table->index('rfid_code');

            // Add FK to employees table if exists
            if (Schema::hasTable('employees')) {
                $table->foreign('employee_id', 'rfid_employee_fk')
                    ->references('id')
                    ->on('employees')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rfid_logs', function (Blueprint $table) {
            // Drop foreign key if exists
            try {
                $table->dropForeign('rfid_employee_fk');
            } catch (\Exception $e) {
                // ignore
            }

            // Drop index and columns if exist
            try {
                $table->dropIndex(['rfid_code']);
            } catch (\Exception $e) {
                // ignore
            }

            if (Schema::hasColumn('rfid_logs', 'event_type')) {
                $table->dropColumn('event_type');
            }

            if (Schema::hasColumn('rfid_logs', 'device_code')) {
                $table->dropColumn('device_code');
            }

            if (Schema::hasColumn('rfid_logs', 'employee_id')) {
                $table->dropColumn('employee_id');
            }

            if (Schema::hasColumn('rfid_logs', 'rfid_code')) {
                $table->dropColumn('rfid_code');
            }
        });
    }
};
