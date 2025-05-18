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
        Schema::create('survey_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('survey_page_id')->constrained()->onDelete('cascade');
            $table->string('limit_condition')->nullable(); // Add: 'at_most', 'equal_to', or null
            $table->integer('max_answers')->nullable();
            $table->text('question_text');
            $table->enum('question_type', [
                'essay', 'multiple_choice', 'page', 'date', 
                'likert', 'radio', 'rating', 'short_text'
            ]);
            $table->integer('stars')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('required')->default(false);
            $table->json('likert_columns')->nullable();
            $table->json('likert_rows')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropColumn(['limit_condition', 'max_answers']);
        });
    }
};
