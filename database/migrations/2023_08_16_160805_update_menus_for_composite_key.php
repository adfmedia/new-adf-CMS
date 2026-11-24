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
        Schema::table('menus', function (Blueprint $table) {
            $table->integer('menu_id')->after('id');
            $table->dropColumn('id');
            $table->primary(['menu_id', 'language_id'], 'menus_pk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropPrimary('menus_pk');
            $table->id()->after('menu_id');
            $table->dropColumn('menu_id');
        });
    }
};
