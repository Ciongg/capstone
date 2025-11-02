<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->text('description')->nullable()->after('merchant_code');
            $table->string('email')->nullable()->after('description');
            $table->string('contact_number')->nullable()->after('email');
            $table->enum('partner_type', ['Affiliate', 'Merchant'])->after('contact_number')->default('Merchant');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['description', 'email', 'contact_number']);
        });
    }
};
