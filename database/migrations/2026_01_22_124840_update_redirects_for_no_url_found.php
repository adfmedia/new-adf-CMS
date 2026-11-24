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
        Schema::table('redirects', function (Blueprint $table) {
            $table->boolean('no_match_only')->default(0)->after('redirect_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redirects', function (Blueprint $table) {
            $table->dropColumn('no_match_only');
        });
    }
};
