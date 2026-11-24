<?php

namespace App\Http\Controllers;

abstract class AuthRestrictedController extends Controller
{
    /**
     * Instance of the logged in user
     * 
     * @var \App\Models\User;
     */
    protected $User;

    /**
     * Constructor - if not logged in, abort &
     * redirect to the login page
     */
    public function __construct()
    {
        parent::__construct();

        $this->User = $this->getUser();
        if (!$this->User) {
            abort(301, '', ['Location' => route('login')]);
        }
    }
}