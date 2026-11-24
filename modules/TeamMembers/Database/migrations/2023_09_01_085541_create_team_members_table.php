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
        Schema::create('team_members', function (Blueprint $table) {
            $table->integer('team_member_id');
            $table->integer('language_id');
            $table->integer('version')->default(1);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('image');
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->text('social_links')->nullable();
            $table->text('short_description')->nullable();
            $table->text('content');
            $table->integer('priority')->default(0);
            $table->boolean('status')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['team_member_id', 'language_id', 'version'], 'team_members_pk');
            $table->index('priority');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};
