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
        Schema::create('events', function (Blueprint $table) {
            $table->integer('event_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('address')->nullable();
            $table->string('image')->nullable();
            $table->text('excerpt');
            $table->text('content');
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['event_id', 'language_id', 'version'], 'events_pk');
            $table->index('event_date');
            $table->index('status');
        });

        Schema::create('event_categories', function (Blueprint $table) {
            $table->integer('event_category_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['event_category_id', 'language_id', 'version'], 'event_cats_pk');
            $table->index('priority');
            $table->index('status');
        });

        Schema::create('event_to_event_category', function (Blueprint $table) {
            $table->integer('event_id');
            $table->integer('event_category_id');
            $table->primary(['event_id', 'event_category_id'], 'e2ec_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_categories');
        Schema::dropIfExists('event_to_event_category');
    }
};
