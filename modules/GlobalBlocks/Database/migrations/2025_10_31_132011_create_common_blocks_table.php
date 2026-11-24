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
        Schema::create('common_blocks', function (Blueprint $table) {
            $table->integer('common_block_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('code_reference')->nullable();
            $table->string('title');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['common_block_id', 'language_id', 'version'], 'common_blocks_pk');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_blocks');
    }
};
