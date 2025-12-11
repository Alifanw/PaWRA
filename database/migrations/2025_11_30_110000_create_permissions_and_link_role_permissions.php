<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create permissions master table
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100)->unique();
                $table->string('guard_name', 50)->nullable();
                $table->text('description')->nullable();
                $table->timestamps(0);
            });
        }

        // Add permission_id to role_permissions (non-destructive)
        if (Schema::hasTable('role_permissions') && !Schema::hasColumn('role_permissions', 'permission_id')) {
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id')->nullable()->after('permission');
            });

            // Backfill: for each distinct permission string, create or get permission id
            $permissions = DB::table('role_permissions')->select('permission')->distinct()->pluck('permission')->filter()->all();

            foreach ($permissions as $permName) {
                $permId = DB::table('permissions')->where('name', $permName)->value('id');
                if (!$permId) {
                    $permId = DB::table('permissions')->insertGetId([
                        'name' => $permName,
                        'guard_name' => null,
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('role_permissions')->where('permission', $permName)->update(['permission_id' => $permId]);
            }

            // Add foreign key constraint
            Schema::table('role_permissions', function (Blueprint $table) {
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->index('permission_id');
            });
        }
    }

    public function down(): void
    {
        // Remove FK and column
        if (Schema::hasTable('role_permissions') && Schema::hasColumn('role_permissions', 'permission_id')) {
            Schema::table('role_permissions', function (Blueprint $table) {
                try { $table->dropForeign(['permission_id']); } catch (\Exception $e) {}
                try { $table->dropIndex(['permission_id']); } catch (\Exception $e) {}
                $table->dropColumn('permission_id');
            });
        }

        // Optionally drop permissions table if exists
        if (Schema::hasTable('permissions')) {
            Schema::dropIfExists('permissions');
        }
    }
};
