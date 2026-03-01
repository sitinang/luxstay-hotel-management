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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_status')->default('waiting')->after('status'); // waiting, arrived, checkout_requested
            $table->timestamp('actual_check_in')->nullable()->after('guest_status');
            $table->timestamp('actual_check_out')->nullable()->after('actual_check_in');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_status', 'actual_check_in', 'actual_check_out']);
        });
    }
};
