<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * 
     * @return void
     */
    public function register(): void
    {
        Vite::useScriptTagAttributes([
            'defer' => true
        ]);
    }

    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot(): void
    {
        // Set default password rules
        Password::defaults(function () {
            return Password::min(8) // Min 8 chars
                ->letters()         // Contains letters
                ->mixedCase()       // Contains upper & lowercase
                ->numbers()         // Contains a number
                ->symbols();        // Contans a special char
        });
    }
}
