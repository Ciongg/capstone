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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'published', 'ongoing', 'finished'])->default('pending');
            $table->enum('type', ['basic', 'advanced'])->default('basic');
            $table->foreignId('survey_topic_id')->nullable()->constrained('survey_topic')->nullOnDelete();

            // Survey Settings
            $table->boolean('is_institution_only')->default(false);
            $table->boolean('is_locked')->default(false); // Added for lock/unlock functionality
            $table->text('lock_reason')->nullable();
            $table->unsignedInteger('target_respondents')->nullable()->default(30);
            $table->unsignedInteger('points_allocated')->nullable();
            $table->unsignedInteger('boost_count')->default(0); // Track number of boosts applied
            $table->string('image_path')->nullable();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Added for archiving functionality
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
