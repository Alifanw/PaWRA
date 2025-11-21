<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('door_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('door_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('event_type', 30); // open, close, forced_open, etc
            $table->timestamp('event_time')->useCurrent();
            $table->string('notes')->nullable();

            $table->timestamps();

            $table->index(['door_id', 'event_time']);
            $table->index('user_id');

            $table->foreign('door_id')->references('id')->on('doors');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('door_events');
    }
};
