<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('id');
                $table->index('employee_id');

                if (Schema::hasTable('employees')) {
                    $table->foreign('employee_id', 'users_employee_fk')
                        ->references('id')->on('employees')
                        ->onDelete('restrict');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            try {
                $table->dropForeign('users_employee_fk');
            } catch (\Exception $e) {
            }

            if (Schema::hasColumn('users', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};
