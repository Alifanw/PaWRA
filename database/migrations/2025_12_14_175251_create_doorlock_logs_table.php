<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doorlock_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable()->comment('Raspberry Pi device identifier');
            $table->string('rfid')->nullable()->comment('RFID card code');
            $table->string('user_id')->nullable()->comment('Associated user ID if identified');
            $table->string('action')->default('scan')->comment('Action type: scan, unlock, error, etc');
            $table->string('status')->default('success')->comment('Status: success, failed, invalid');
            $table->string('door_status')->nullable()->comment('Door state: closed, open');
            $table->integer('door_duration')->nullable()->comment('Door open duration in seconds');
            $table->text('notes')->nullable()->comment('Additional notes or error message');
            $table->string('ip_address')->nullable()->comment('Source IP address');
            $table->json('metadata')->nullable()->comment('Additional metadata from Raspberry Pi');
            $table->timestamps();
            
            $table->index('rfid');
            $table->index('device_id');
            $table->index('user_id');
            $table->index('created_at');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doorlock_logs');
    }
};
