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
        Schema::create('security_logs', function (Blueprint $table) {
                $table->bigIncrements('id');

        // Optional UUID for easier external reference or correlation
        $table->uuid('uuid')->nullable()->index(); // e.g., "a13f8b1a-2c44-4f1d-8a6a-11f35f0dbb12"

        // Timestamp of event creation
        $table->timestamp('created_at')->useCurrent();

        // Type of event
        $table->string('event_type')->index(); 
        // Examples:
        // - "login_attempt"
        // - "login_success"
        // - "login_failure"
        // - "access_denied"
        // - "csrf_blocked"
        // - "rate_limit_triggered"
        // - "session_expired"
        // - "mfa_challenge_failed"

        // Outcome or result of the event (success, failure, denied, etc.)
        $table->string('outcome')->nullable(); 
        // e.g., "success", "failure", "denied"

        // The user associated with the event (if applicable)
        $table->unsignedBigInteger('user_id')->nullable()->index(); 
        // e.g., 42 (user who attempted login)

        // The user's role or privilege level at the time
        $table->string('actor_role')->nullable(); 
        // e.g., "researcher", "institution_admin", "super_admin", "guest"

        // IP address of the request (IPv4 or IPv6)
        $table->string('ip', 45)->nullable()->index(); 
        // e.g., "192.168.1.101"

        // Full user-agent string
        $table->text('user_agent')->nullable(); 
        // e.g., "Mozilla/5.0 (Windows NT 10.0; Win64; x64)..."

        // Route or endpoint accessed
        $table->string('route')->nullable()->index(); 
        // e.g., "/admin/login" or "api/surveys/12"

        // HTTP method used
        $table->string('http_method', 10)->nullable(); 
        // e.g., "POST", "GET"

        // HTTP response status code
        $table->integer('http_status')->nullable(); 
        // e.g., 200, 401, 403, 500

        // If event relates to a specific model/resource (optional)
        $table->string('resource_type')->nullable(); 
        // e.g., "Survey", "User", "Institution"

        // ID of the resource (if applicable)
        $table->unsignedBigInteger('resource_id')->nullable(); 
        // e.g., 15 (for Survey #15)

        // Short summary of the event
        $table->string('message', 255)->nullable(); 
        // e.g., "Failed login attempt for super_admin@formigo.com"

        // Additional structured data related to event
        $table->json('meta')->nullable(); 
        // e.g., {"attempt_count":3,"mfa_required":true,"route_name":"admin.login"}

        // Optional geolocation info (can be filled via GeoIP)
        $table->json('geo')->nullable(); 
        // e.g., {"country":"PH","city":"Manila","lat":14.5995,"lon":120.9842}

        // Combined index for querying by type and time
        $table->index(['event_type', 'created_at']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_logs');
    }
};
