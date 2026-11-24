<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleMigrationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot(): void
    {
        $module_migration_dirs = self::getModuleMigrationPaths();
        $this->loadMigrationsFrom($module_migration_dirs);
    }

    /**
     * Return the Database/migration paths within available modules
     * 
     * @return array
     */
    public static function getModuleMigrationPaths(): array
    {
        $modules_to_migrate = array_filter(config('modules.available'), function ($module) {
            return is_dir(base_path('modules/' . $module . '/Database/migrations'));
        });
        return array_map(function ($module) {
            return 'modules/' . $module . '/Database/migrations';
        }, $modules_to_migrate);
    }
}