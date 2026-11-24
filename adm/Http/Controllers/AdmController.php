<?php

namespace Adm\Http\Controllers;

use Adm\Crud\CrudInterface;
use Adm\Helpers\ViewHelper;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


abstract class AdmController extends BaseController
{
    use ValidatesRequests;

    /**
     * Local CrudInterface instance available to CMS
     * controllers
     * 
     * @var \Adm\Crud\CrudInterface
     */
    protected $Crud;
    
    /**
     * Construct a new controller instance, setting the
     * view path to the adm folder & creating a 
     * CrudInterface instance
     */
    public function __construct()
    {
        ViewHelper::setViews();
        $this->Crud = new CrudInterface();
    }
}