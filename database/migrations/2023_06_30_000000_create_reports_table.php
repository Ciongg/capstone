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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->onDelete('cascade');
            $table->foreignId('response_id')->constrained('responses')->onDelete('cascade');
            $table->foreignId('reporter_id')->comment('User ID who reported')->constrained('users')->onDelete('cascade');
            $table->foreignId('respondent_id')->nullable()->comment('User ID who got reported')->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('question_id')->nullable()->comment('References survey_questions.id');
            $table->string('reason');
            $table->text('details');
            $table->timestamps();
            
            // Add indexes for faster queries
            $table->index(['survey_id', 'status']);
            $table->index(['reporter_id', 'created_at']);
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
