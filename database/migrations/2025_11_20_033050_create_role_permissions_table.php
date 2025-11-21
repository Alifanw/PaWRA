<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedSmallInteger('role_id');
            $table->string('permission', 64);
            $table->primary(['role_id', 'permission']);
            $table->foreign('role_id')
                  ->references('id')
                  ->on('roles')
                  ->onDelete('cascade');
            $table->index('permission');
            $table->timestamps(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};

// ...existing code...