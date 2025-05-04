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
            $table->timestamps();
            
            //Survey Settings
            $table->decimal('points')->nullable()->default(0);
            $table->unsignedInteger('target_respondents')->nullable()->default(30);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->unsignedInteger('points_allocated')->nullable();
            $table->string('image_path')->nullable();
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
