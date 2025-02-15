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
        Schema::create('episodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('podcast_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('audio_url', 2048);
            $table->string('image_url', 2048);
            $table->unsignedInteger('duration');
            $table->dateTime('published_at');
            $table->timestamps();

            $table->unique(['podcast_id', 'audio_url']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
