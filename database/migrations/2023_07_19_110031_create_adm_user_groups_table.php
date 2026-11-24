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
        Schema::create('adm_user_groups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('permissions')->nullable();
            $table->boolean('super_admin')->default(0);
            $table->boolean('active')->default(0);
            $table->timestamps();
        });

        Schema::table('adm_users', function (Blueprint $table) {
            $table->integer('adm_user_group_id')->default(0)->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_user_groups');

        Schema::table('adm_users', function (Blueprint $table) {
            $table->dropColumn('adm_user_group_id');
        });
    }
};
