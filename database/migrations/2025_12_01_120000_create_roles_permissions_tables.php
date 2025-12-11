<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_role')) {
            Schema::create('permission_role', function (Blueprint $table) {
                // roles.id was created with smallIncrements in earlier migrations
                // use unsignedSmallInteger to match that existing type
                $table->unsignedSmallInteger('role_id');
                // permissions.id uses big integers
                $table->unsignedBigInteger('permission_id');
                $table->timestamps();

                $table->primary(['role_id','permission_id']);

                // add foreign keys with explicit types
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // If older tables reference `permissions` (e.g. `role_permissions`), drop their FKs first
        if (Schema::hasTable('role_permissions')) {
            Schema::table('role_permissions', function (Blueprint $table) {
                try { $table->dropForeign(['permission_id']); } catch (\Exception $e) {}
                try { $table->dropIndex(['permission_id']); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('permission_role')) {
            Schema::table('permission_role', function (Blueprint $table) {
                try { $table->dropForeign(['permission_id']); } catch (\Exception $e) {}
                try { $table->dropForeign(['role_id']); } catch (\Exception $e) {}
                try { $table->dropIndex(['permission_id']); } catch (\Exception $e) {}
                try { $table->dropIndex(['role_id']); } catch (\Exception $e) {}
            });
            Schema::dropIfExists('permission_role');
        }

        if (Schema::hasTable('permissions')) {
            Schema::dropIfExists('permissions');
        }

        if (Schema::hasTable('roles')) {
            Schema::dropIfExists('roles');
        }
    }
};
