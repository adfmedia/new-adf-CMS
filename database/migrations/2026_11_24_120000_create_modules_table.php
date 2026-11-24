<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the module registry and record the modules this site already
     * uses (from config/modules.php) as enabled, so nothing changes on
     * existing sites.
     */
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('version', 30)->default('0.0.0');
            $table->string('status', 20)->default('enabled')->index(); // enabled | disabled | uninstalled
            $table->boolean('is_core')->default(false);
            $table->json('manifest')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        // Read the config file directly so a cached or overridden value can't interfere
        $declared = (array) ((require config_path('modules.php'))['available'] ?? []);
        $now = now();

        foreach ($declared as $name) {
            $manifest_path = base_path('modules/' . $name . '/module.json');
            if (!is_dir(base_path('modules/' . $name))) {
                continue;
            }

            $manifest = is_file($manifest_path) ? json_decode(file_get_contents($manifest_path), true) : [];
            $manifest = is_array($manifest) ? $manifest : [];

            DB::table('modules')->insert([
                'name'              => $name,
                'version'           => $manifest['version'] ?? '0.0.0',
                'status'            => 'enabled',
                'is_core'           => (bool) ($manifest['core'] ?? false),
                'manifest'          => json_encode($manifest),
                'installed_at'      => $now,
                'status_changed_at' => $now,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // Make the next request rebuild its module cache from this table
        @unlink(base_path('bootstrap/cache/modules.php'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
        @unlink(base_path('bootstrap/cache/modules.php'));
    }
};
