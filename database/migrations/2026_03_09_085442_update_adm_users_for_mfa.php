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
        Schema::table('adm_users', function (Blueprint $table) {
            $table->boolean('mfa_enrolled')->default(0)->after('adm_user_group_id');
            $table->string('mfa_secret')->nullable()->after('mfa_enrolled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_users', function (Blueprint $table) {
            $table->dropColumn('mfa_enrolled');
            $table->dropColumn('mfa_secret');
        });
    }
};
