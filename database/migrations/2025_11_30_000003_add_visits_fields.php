<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('visits')) {
            return;
        }

        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'visit_token')) {
                $table->string('visit_token', 64)->nullable()->after('id');
            }
            if (!Schema::hasColumn('visits', 'status')) {
                $table->enum('status', ['available','checked_in','checked_out','revoked'])->default('available')->after('visit_token');
            }
            if (!Schema::hasColumn('visits', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('visits', 'checked_in_by')) {
                $table->unsignedBigInteger('checked_in_by')->nullable()->after('checked_in_at');
                $table->foreign('checked_in_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('visits', 'checked_out_at')) {
                $table->timestamp('checked_out_at')->nullable()->after('checked_in_by');
            }
            if (!Schema::hasColumn('visits', 'checked_out_by')) {
                $table->unsignedBigInteger('checked_out_by')->nullable()->after('checked_out_at');
                $table->foreign('checked_out_by')->references('id')->on('users')->nullOnDelete();
            }

            $table->index(['status','checked_in_at','checked_out_at']);
            // add visit_token index only if it doesn't already exist
            $exists = \Illuminate\Support\Facades\DB::select(
                "SHOW INDEX FROM `visits` WHERE Key_name = ?",
                ['visits_visit_token_index']
            );
            if (empty($exists)) {
                $table->index('visit_token');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('visits')) {
            return;
        }

        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'checked_out_by')) {
                $table->dropForeign(['checked_out_by']);
                $table->dropColumn('checked_out_by');
            }
            if (Schema::hasColumn('visits', 'checked_out_at')) {
                $table->dropColumn('checked_out_at');
            }
            if (Schema::hasColumn('visits', 'checked_in_by')) {
                $table->dropForeign(['checked_in_by']);
                $table->dropColumn('checked_in_by');
            }
            if (Schema::hasColumn('visits', 'checked_in_at')) {
                $table->dropColumn('checked_in_at');
            }
            if (Schema::hasColumn('visits', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('visits', 'visit_token')) {
                $exists = \Illuminate\Support\Facades\DB::select(
                    "SHOW INDEX FROM `visits` WHERE Key_name = ?",
                    ['visits_visit_token_index']
                );
                if (!empty($exists)) {
                    $table->dropIndex(['visit_token']);
                }
                $table->dropColumn('visit_token');
            }
        });
    }
};
