<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->enum('target_audience', ['public', 'institution_specific']);
            $table->foreignId('institution_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->string('url')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamps();

            $table->foreignId('survey_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('announcements');
    }
};
