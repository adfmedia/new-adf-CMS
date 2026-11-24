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
        Schema::create('search_index', function (Blueprint $table) {
            $table->id();
            $table->integer('url_id');
            $table->integer('language_id');
            $table->string('url');
            $table->text('content');
            $table->timestamps();
            $table->index('url_id');
            $table->fullText('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_index');
    }
};
