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
        Schema::create('offices', function (Blueprint $table) {
            $table->integer('office_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('title');
            $table->string('h1_title')->nullable();
            $table->string('image')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('address')->nullable();
            $table->text('opening_times')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['office_id', 'language_id', 'version'], 'offices_pk');
            $table->index('priority');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
