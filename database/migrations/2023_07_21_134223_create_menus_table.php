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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->integer('language_id');
            $table->string('title');
            $table->string('code_reference');
            $table->text('structure')->nullable();
            $table->integer('nest_limit')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->index('code_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
