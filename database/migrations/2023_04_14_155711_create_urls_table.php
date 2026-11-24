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
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->integer('urlable_id');
            $table->string('urlable_type');
            $table->integer('language_id');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->boolean('no_index')->default(0);
            $table->boolean('system_page')->default(0);
            $table->string('route')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('url');
            $table->index('language_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
