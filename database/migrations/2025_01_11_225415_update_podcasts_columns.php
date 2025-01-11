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
        Schema::table('podcasts', function (Blueprint $table) {
            $table->text('description')->change();
            $table->text('image_url')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            $table->string('description')->change();
            $table->string('image_url')->change();
        });
    }
};
