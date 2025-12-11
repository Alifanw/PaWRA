<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('door_events', function (Blueprint $table) {
            if (Schema::hasColumn('door_events', 'user_id')) {
                try {
                    $table->dropForeign(['user_id']);
                } catch (\Exception $e) {
                    // ignore if FK not present or name differs
                }

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('door_events', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Exception $e) {
            }

            // restore default FK without cascade
            if (Schema::hasColumn('door_events', 'user_id')) {
                $table->foreign('user_id')->references('id')->on('users');
            }
        });
    }
};
