<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Dotenv\Dotenv;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            append: [
                \App\Http\Middleware\SecurityHeaders::class,
                \App\Http\Middleware\AddCspHeaders::class
            ]
        );
        
        $middleware->alias([
            'auth'  => App\Http\Middleware\Authenticate::class,
            'guest' => App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        $middleware->appendToGroup('front', [
            App\Http\Middleware\Redirect::class,
            App\Http\Middleware\ContentReplacement::class,
        ]);

        $middleware->preventRequestForgery(except: [
            'beanstalk/pre',
            'beanstalk/post',
            'ajax/ping/token'
        ]);

        $middleware->preventRequestsDuringMaintenance(except: [
            'beanstalk/pre',
            'beanstalk/post'
        ]);

        $middleware->encryptCookies(except: [

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson()
        );
    })
    ->create();

$app->detectEnvironment(function () {
    $my_env = '';

    $environment_path = dirname(dirname(__FILE__)) . '/.environment';
    if (file_exists($environment_path)) {
        $my_env = trim(file_get_contents($environment_path));
    }

    $env_path = dirname(dirname(__FILE__)) . '/.env' . (!empty($my_env) ? '.' . $my_env : '');
    if (file_exists($env_path)) {
        $dotenv = Dotenv::createImmutable(dirname(dirname(__FILE__)) . '/', '.env' . (!empty($my_env) ? '.' . $my_env : ''));
        $dotenv->load();
    } else {
        die('The app environment is not configured correctly.');
    }
});

return $app;