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
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('voucher_id')->constrained()->onDelete('cascade'); // Link to the specific voucher instance
            $table->foreignId('reward_redemption_id')->constrained()->onDelete('cascade'); // Link to the redemption transaction
            $table->enum('status', ['available', 'unavailable', 'active', 'used', 'expired'])->default('available'); // Updated status options
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'voucher_id']); // A user can own a specific voucher instance only once
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
    }
};
