<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        // Add missing JSON columns if they don't exist
        if (!Schema::hasColumn('audit_logs', 'before')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->json('before')->nullable()->after('resource_id');
            });
        }

        if (!Schema::hasColumn('audit_logs', 'after')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->json('after')->nullable()->after('before');
            });
        }

        // Ensure ip_address column exists (legacy column may be ip_addr)
        if (!Schema::hasColumn('audit_logs', 'ip_address')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->after('after');
            });
        }

        // Ensure user_agent exists and is sufficiently long
        if (!Schema::hasColumn('audit_logs', 'user_agent')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->string('user_agent')->nullable()->after('ip_address');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'before')) {
                $table->dropColumn('before');
            }
            if (Schema::hasColumn('audit_logs', 'after')) {
                $table->dropColumn('after');
            }
            if (Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->dropColumn('ip_address');
            }
            if (Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });
    }
};
