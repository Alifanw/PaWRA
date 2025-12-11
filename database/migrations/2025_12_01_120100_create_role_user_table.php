<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                // roles.id uses smallIncrements in earlier migration, use unsignedSmallInteger
                $table->unsignedSmallInteger('role_id');
                $table->timestamps();

                $table->primary(['user_id','role_id']);
                $table->index('role_id');

                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
