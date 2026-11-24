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
        Schema::create('news_categories', function (Blueprint $table) {
            $table->integer('news_category_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->string('h1_title')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['news_category_id', 'language_id', 'version'], 'news_categories_pk');
            $table->index('status');
        });

        Schema::create('news_authors', function (Blueprint $table) {
            $table->integer('news_author_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->string('image')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['news_author_id', 'language_id', 'version'], 'news_authors_pk');
            $table->index('status');
        });

        Schema::create('news_articles', function (Blueprint $table) {
            $table->integer('news_article_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->integer('news_category_id');
            $table->integer('news_author_id')->default(0);
            $table->string('title');
            $table->string('h1_title')->nullable();
            $table->text('excerpt');
            $table->text('content');
            $table->string('image');
            $table->date('article_date');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['news_article_id', 'language_id', 'version'], 'news_articles_pk');
            $table->index('article_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_categories');
        Schema::dropIfExists('news_authors');
        Schema::dropIfExists('news_articles');
    }
};
