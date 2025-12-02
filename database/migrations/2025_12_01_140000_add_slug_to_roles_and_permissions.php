<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Add slug to roles if missing
        if (Schema::hasTable('roles') && !Schema::hasColumn('roles', 'slug')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->string('slug', 100)->nullable()->after('name');
            });

            // Backfill slug values safely using PHP to avoid DB-specific regex
            $roles = DB::table('roles')->select('id', 'name')->get();
            foreach ($roles as $r) {
                $slug = Str::slug($r->name, '_');
                // ensure uniqueness by appending id if conflict
                $exists = DB::table('roles')->where('slug', $slug)->where('id', '!=', $r->id)->exists();
                if ($exists) {
                    $slug = $slug . '_' . $r->id;
                }
                DB::table('roles')->where('id', $r->id)->update(['slug' => $slug]);
            }

            // Try to add unique index on slug if no duplicates
            try {
                $duplicates = DB::table('roles')
                    ->select('slug', DB::raw('count(*) as cnt'))
                    ->groupBy('slug')
                    ->havingRaw('count(*) > 1')
                    ->get();

                if ($duplicates->isEmpty()) {
                    Schema::table('roles', function (Blueprint $table) {
                        $table->unique('slug');
                    });
                }
            } catch (\Exception $e) {
                // safe to ignore; leave column as nullable/index-free
            }
        }

        // Add slug to permissions if missing
        if (Schema::hasTable('permissions') && !Schema::hasColumn('permissions', 'slug')) {
            Schema::table('permissions', function (Blueprint $table) {
                $table->string('slug', 100)->nullable()->after('name');
            });

            $perms = DB::table('permissions')->select('id', 'name')->get();
            foreach ($perms as $p) {
                $slug = Str::slug($p->name, '_');
                $exists = DB::table('permissions')->where('slug', $slug)->where('id', '!=', $p->id)->exists();
                if ($exists) {
                    $slug = $slug . '_' . $p->id;
                }
                DB::table('permissions')->where('id', $p->id)->update(['slug' => $slug]);
            }

            try {
                $duplicates = DB::table('permissions')
                    ->select('slug', DB::raw('count(*) as cnt'))
                    ->groupBy('slug')
                    ->havingRaw('count(*) > 1')
                    ->get();

                if ($duplicates->isEmpty()) {
                    Schema::table('permissions', function (Blueprint $table) {
                        $table->unique('slug');
                    });
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'slug')) {
            Schema::table('roles', function (Blueprint $table) {
                try {
                    $table->dropUnique(['roles_slug_unique']);
                } catch (\Exception $e) {
                }
                $table->dropColumn('slug');
            });
        }

        if (Schema::hasTable('permissions') && Schema::hasColumn('permissions', 'slug')) {
            Schema::table('permissions', function (Blueprint $table) {
                try {
                    $table->dropUnique(['permissions_slug_unique']);
                } catch (\Exception $e) {
                }
                $table->dropColumn('slug');
            });
        }
    }
};
