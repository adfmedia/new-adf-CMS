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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->integer('file_folder_id');
            $table->string('storage_path');
            $table->string('file_path');
            $table->string('title');
            $table->string('alt_text')->nullable();
            $table->string('extension')->nullable();
            $table->integer('width')->default(0);
            $table->integer('height')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('file_path');
            $table->index('file_folder_id');
        });

        Schema::create('file_folders', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(0);
            $table->string('path');
            $table->string('title');
            $table->timestamps();
            $table->softDeletes();
            $table->index('path');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
        Schema::dropIfExists('file_folders');
    }
};
