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
        Schema::create('case_studies', function (Blueprint $table) {
            $table->integer('case_study_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->string('h1_title')->nullable();
            $table->string('image');
            $table->text('excerpt')->nullable();
            $table->text('content');
            $table->date('date');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['case_study_id', 'language_id', 'version'], 'case_studies_pk');
            $table->index('date');
            $table->index('status');
        });

        Schema::create('case_study_categories', function (Blueprint $table) {
            $table->integer('case_study_category_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->string('h1_title')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['case_study_category_id', 'language_id', 'version'], 'case_study_categories_pk');
            $table->index('status');
        });

        Schema::create('case_study_to_case_study_category', function (Blueprint $table) {
            $table->integer('case_study_id');
            $table->integer('case_study_category_id');
            $table->primary(['case_study_id', 'case_study_category_id'], 'cs2csc_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_studies');
        Schema::dropIfExists('case_study_categories');
        Schema::dropIfExists('case_study_to_case_study_category');
    }
};
