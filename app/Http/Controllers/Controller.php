<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use ValidatesRequests;

    /**
     * Get the currently logged in user, returning
     * the user instance if logged in, otherwise null
     * 
     * @return ?\App\Models\User
     */
    protected function getUser(): ?User
    {
        return Auth::user();
    }
}
