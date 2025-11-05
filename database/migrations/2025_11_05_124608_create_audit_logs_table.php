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
        Schema::create('audit_logs', function (Blueprint $table) {
                $table->bigIncrements('id');

        // Timestamp of the action
        $table->timestamp('created_at')->useCurrent();

        // User who performed the action (admin or system)
        $table->unsignedBigInteger('performed_by')->nullable()->index(); 
        // e.g., 3 (super admin ID)

        // Role of the user who performed the action
        $table->string('performed_role')->nullable(); 
        // e.g., "super_admin", "institution_admin"

        // Type of action performed
        $table->string('event_type')->index(); 
        // e.g.:
        // - "admin_edit_user"
        // - "admin_archive_user"
        // - "lock_survey"
        // - "unlock_survey"
        // - "grant_admin_role"
        // - "delete_institution"

        // Type of resource being acted on
        $table->string('resource_type')->nullable(); 
        // e.g., "User", "Survey", "Institution"

        // ID of the affected resource
        $table->unsignedBigInteger('resource_id')->nullable()->index(); 
        // e.g., 58 (User #58 that was edited)

        // JSON snapshot of data before change
        $table->json('before')->nullable(); 
        // e.g., {"status":"active","email":"old@email.com"}

        // JSON snapshot of data after change
        $table->json('after')->nullable(); 
        // e.g., {"status":"archived","email":"new@email.com"}

        // Fields that changed (for quick diff reference)
        $table->json('changed_fields')->nullable(); 
        // e.g., ["status","email"]

        // IP address of the actor
        $table->string('ip', 45)->nullable(); 
        // e.g., "203.177.42.11"

        // User-agent string of the actor
        $table->text('user_agent')->nullable(); 
        // e.g., "Mozilla/5.0 (Windows NT 10.0; Win64; x64)..."

        // Short summary message of the action
        $table->string('message', 255)->nullable(); 
        // e.g., "Super admin archived User #58"

        // Extra structured data (context or justifications)
        $table->json('meta')->nullable(); 
        // e.g., {"reason":"user inactive for 6 months"}

        // Index for common queries
        $table->index(['performed_by', 'created_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
