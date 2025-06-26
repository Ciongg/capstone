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
        Schema::create('institution_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_tag_category_id')->constrained()->onDelete('cascade');
            $table->string('name'); // e.g., "Computer Science", "Faculty", "18-24"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institution_tags');
    }
};
