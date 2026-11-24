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
        Schema::create('faqs', function (Blueprint $table) {
            $table->integer('faq_id');
            $table->integer('language_id');
            $table->string('title');
            $table->string('question');
            $table->text('answer');
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['faq_id', 'language_id'], 'faqs_pk');
            $table->index('priority');
            $table->index('status');
        });

        Schema::create('faq_categories', function (Blueprint $table) {
            $table->integer('faq_category_id');
            $table->integer('language_id');
            $table->string('title');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['faq_category_id', 'language_id'], 'faq_categories_pk');
            $table->index('status');
        });

        Schema::create('faq_to_faq_category', function (Blueprint $table) {
            $table->integer('faq_id');
            $table->integer('faq_category_id');
            $table->primary(['faq_id', 'faq_category_id'], 'f2fc_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('faq_categories');
        Schema::dropIfExists('faq_to_faq_category');
    }
};
