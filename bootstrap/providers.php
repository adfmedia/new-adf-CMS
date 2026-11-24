<?php

return [
    // Custom App service provider (currently unused)
    App\Providers\AppServiceProvider::class,

    // Custom Auth service provider (reset password links for both FE & CMS users)
    App\Providers\AuthServiceProvider::class,

    // Custom Migration service provider (run DB migrations in modules)
    App\Providers\ModuleMigrationServiceProvider::class,

    // Service provider for Laravel HTML package
    Spatie\Html\HtmlServiceProvider::class,
];
