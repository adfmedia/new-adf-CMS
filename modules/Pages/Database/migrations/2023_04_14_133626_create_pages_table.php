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
        Schema::create('pages', function (Blueprint $table) {
            $table->integer('page_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->string('h1_title')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['page_id', 'language_id', 'version'], 'pages_pk');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
