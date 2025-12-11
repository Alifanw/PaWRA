<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('booking_code', 30)->unique();
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20);
            $table->dateTime('checkin');
            $table->dateTime('checkout');
            $table->tinyInteger('night_count')->unsigned();
            $table->tinyInteger('room_count')->unsigned()->default(1);
            $table->enum('status', [
                'draft','pending','confirmed','checked_in','checked_out','cancelled'
            ])->default('pending');
            $table->decimal('dp_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

            $table->index(['status', 'checkin'], 'idx_bookings_status');
            $table->index('created_by', 'idx_bookings_created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        // SQLite does not support ALTER TABLE ... ADD CONSTRAINT in the same
        // way as MySQL/Postgres. When running tests with the sqlite in-memory
        // driver, skip adding the check constraint to avoid SQL errors.
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_booking_dates CHECK (checkout > checkin)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
