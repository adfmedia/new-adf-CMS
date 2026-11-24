<?php

namespace Modules\Offices\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\Offices\Config\OfficesConfig;

class OfficesController extends AdmController
{
    public function offices($method, $id)
    {
        return $this->Crud->boot($method, $id, OfficesConfig::class);
    }
}