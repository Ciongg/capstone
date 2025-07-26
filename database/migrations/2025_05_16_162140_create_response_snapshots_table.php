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
        Schema::create('response_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->unique()->constrained()->onDelete('cascade');
            // Basic user info
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            // User metrics
            $table->decimal('trust_score', 10, 0)->default(100);
            $table->decimal('points', 10, 0)->default(0);
            $table->integer('account_level')->default(0);
            $table->decimal('experience_points', 10, 0)->default(0);
            $table->enum('rank', ['silver', 'gold', 'diamond'])->default('silver');
            $table->string('title')->nullable();
            // Time tracking
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('completion_time_seconds')->nullable();
            // Demographic data
            $table->json('demographic_tags')->nullable(); // Store all tags associated with user
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('response_snapshots');
    }
};
