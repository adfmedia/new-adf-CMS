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
        Schema::create('page_builder_blocks', function (Blueprint $table) {
            $table->id();
            $table->integer('morphable_id');
            $table->string('morphable_type');
            $table->string('morphable_field');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('block_class');
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['morphable_id', 'morphable_type', 'morphable_field', 'language_id', 'version'], 'search_idx');
        });

        Schema::create('page_builder_block_contents', function (Blueprint $table) {
            $table->id();
            $table->integer('page_builder_block_id');
            $table->string('field');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->index('page_builder_block_id', 'parent_block');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_builder_blocks');
        Schema::dropIfExists('page_builder_block_contents');
    }
};
