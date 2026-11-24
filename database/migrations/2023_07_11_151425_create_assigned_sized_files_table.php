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
        Schema::create('assigned_sized_files', function (Blueprint $table) {
            $table->id();
            $table->integer('morphable_id');
            $table->string('morphable_type');
            $table->string('morphable_field');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('size');
            $table->integer('file_id');
            $table->integer('width')->default(0);
            $table->integer('height')->default(0);
            $table->string('render_style')->nullable();
            $table->integer('crop_w')->default(0);
            $table->integer('crop_h')->default(0);
            $table->integer('crop_x')->default(0);
            $table->integer('crop_y')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['morphable_id', 'morphable_type', 'morphable_field', 'language_id', 'version'], 'assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assigned_sized_files');
    }
};
