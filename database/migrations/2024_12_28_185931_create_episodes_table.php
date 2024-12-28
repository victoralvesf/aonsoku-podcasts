<?php

use App\Models\Podcast;
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
            $table->id();
            $table->foreignIdFor(Podcast::class, 'podcast_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('audio_url')->unique();
            $table->string('image_url');
            $table->integer('duration');
            $table->dateTime('published_at');
            $table->timestamps();
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
