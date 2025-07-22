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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reward_id')->constrained()->onDelete('cascade'); // Link to the parent reward
            $table->string('reference_no')->unique(); // Unique code for this specific voucher instance
            $table->string('store_name');
            $table->string('promo');
            $table->integer('cost'); // Copied from reward for reference
            $table->timestamp('expiry_date')->nullable();
            $table->enum('availability', ['available', 'unavailable', 'expired', 'used'])->default('available');
            $table->string('image_path')->nullable(); // Copied from reward
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
