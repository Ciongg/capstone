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
        Schema::table('security_logs', function (Blueprint $table) {
            $table->string('email')->nullable()->after('created_at')->index();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('email')->nullable()->after('created_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('security_logs', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
