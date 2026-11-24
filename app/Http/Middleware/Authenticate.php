<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        $action = $request->route()->getAction();
        if (!empty($action) && $action['prefix'] == '/adm') {
            return route('adm.login');
        }

        return $request->expectsJson() ? null : route('login');
    }
}
