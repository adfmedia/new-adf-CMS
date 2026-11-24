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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->integer('testimonial_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->text('quote');
            $table->string('citation');
            $table->string('job_title')->nullable();
            $table->string('image');
            $table->date('date');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['testimonial_id', 'language_id', 'version'], 'testimonials_pk');
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
