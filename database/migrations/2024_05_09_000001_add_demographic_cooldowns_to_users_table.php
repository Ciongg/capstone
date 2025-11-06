<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('demographic_tag_cooldowns')->nullable()->after('profile_updated_at');
            $table->json('institution_demographic_tag_cooldowns')->nullable()->after('demographic_tag_cooldowns');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['demographic_tag_cooldowns', 'institution_demographic_tag_cooldowns']);
        });
    }
};
