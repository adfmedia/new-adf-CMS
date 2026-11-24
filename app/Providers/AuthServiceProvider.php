<?php

namespace App\Providers;

use Adm\Models\AdmUser;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as BaseServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;

class AuthServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap any application services.
     * 
     * @return void
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (Model $User, string $token) {
            if ($User instanceof AdmUser) {
                return route('adm.reset-password', [
                    'token' => $token,
                    'email' => $User->getEmailForPasswordReset()
                ]);
            }

            return route('password.reset', [
                'token' => $token,
                'email' => $User->getEmailForPasswordReset()
            ]);
        });
    }
}