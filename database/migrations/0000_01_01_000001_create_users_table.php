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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number')->unique()->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            
            $table->decimal('points', 10, 0)->default(0);
            $table->decimal('trust_score', 10, 0)->default(100);
            $table->integer('account_level')->default(1);
            $table->decimal('experience_points', 10, 0)->default(0);
            $table->enum('rank', ['silver', 'gold', 'diamond'])->default('silver');
            $table->string('title')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('type', ['respondent', 'researcher', 'institution_admin', 'super_admin'])->default('respondent');
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->onDelete('set null'); // Add this line
            $table->string('password');
            $table->string('profile_photo_path', 2048)->nullable(); 
            $table->rememberToken();
            $table->timestamps();
            $table->timestamp('last_active_at')->nullable(); // Add this line
            $table->timestamp('demographic_tags_updated_at')->nullable(); // Add this line
            $table->timestamp('profile_updated_at')->nullable(); // Add this line for profile update tracking
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
