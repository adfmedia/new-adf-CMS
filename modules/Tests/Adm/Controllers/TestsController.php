<?php

namespace Modules\Tests\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\Tests\Config\TestsConfig;

class TestsController extends AdmController
{
    public function tests($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            TestsConfig::class
        );
    }
}