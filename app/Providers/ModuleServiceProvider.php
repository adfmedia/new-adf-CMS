<?php

namespace App\Providers;

use App\Modules\Module;
use App\Modules\ModuleRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Registers each enabled module's views, migrations and routes, so a module
 * folder carries everything it needs.
 *
 * Views:      modules/<Name>/Views       -> <namespace>::view.name
 *             (a site can override any of them in resources/views/vendor/<namespace>)
 * Migrations: modules/<Name>/Database/migrations
 * Routes:     modules/<Name>/routes.php  (optional)
 * Assets:     modules/<Name>/Public      -> public/modules/<namespace> (cms:modules:publish)
 */
class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register the module repository
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(ModuleRepository::class);
    }

    /**
     * Wire up each enabled module
     *
     * @return void
     */
    public function boot(): void
    {
        $Repository = $this->app->make(ModuleRepository::class);

        // config('modules.available') now reflects the registry, so existing
        // code that reads it only sees enabled modules. The list declared in
        // config/modules.php stays available as config('modules.declared').
        if (config('modules.declared') === null) {
            config(['modules.declared' => (array) config('modules.available', [])]);
        }
        config(['modules.available' => $Repository->enabledNames()]);

        $modules = $Repository->enabled();

        foreach ($modules as $Module) {
            $this->bootModule($Module);
        }
    }

    /**
     * Register one module's views, migrations and routes
     *
     * @param Module $Module
     * @return void
     */
    protected function bootModule(Module $Module): void
    {
        if (is_dir($Module->viewsPath())) {
            $this->loadViewsFrom($Module->viewsPath(), $Module->viewNamespace());
        }

        if (is_dir($Module->migrationsPath())) {
            $this->loadMigrationsFrom($Module->migrationsPath());
        }

        $routes = $Module->subPath('routes.php');
        if (is_file($routes) && !$this->app->routesAreCached()) {
            $this->loadRoutesFrom($routes);
        }
    }

    /**
     * Return the Database/migrations paths (relative to the base path) of
     * enabled modules, for running module migrations one path at a time
     *
     * @return array
     */
    public static function getModuleMigrationPaths(): array
    {
        $paths = [];
        foreach (app(ModuleRepository::class)->enabled() as $Module) {
            if (is_dir($Module->migrationsPath())) {
                $paths[] = 'modules/' . $Module->name . '/Database/migrations';
            }
        }

        return $paths;
    }
}
