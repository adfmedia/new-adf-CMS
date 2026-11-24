<?php

return [
    // Custom App service provider (currently unused)
    App\Providers\AppServiceProvider::class,

    // Custom Auth service provider (reset password links for both FE & CMS users)
    App\Providers\AuthServiceProvider::class,

    // Module service provider (module views, migrations, routes)
    App\Providers\ModuleServiceProvider::class,

    // Service provider for Laravel HTML package
    Spatie\Html\HtmlServiceProvider::class,
];
