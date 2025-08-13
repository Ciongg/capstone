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
        Schema::create('support_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->string('request_type'); // survey_lock_appeal, report_appeal, account_issue, survey_question, other
            $table->string('status')->default('pending'); // pending, in_progress, resolved, rejected
            $table->string('related_id')->nullable(); // For storing related entity ID (survey ID, report ID, etc)
            $table->string('related_model')->nullable(); // For storing the model name of the related entity
            $table->text('admin_notes')->nullable(); // For admin responses and notes
            $table->foreignId('admin_id')->nullable()->constrained('users'); // Admin who handled the request
            $table->timestamp('resolved_at')->nullable(); // When the request was resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_requests');
    }
};
