<?php

namespace Modules\GlobalBlocks\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\GlobalBlocks\Config\CommonBlocksConfig;

class GlobalBlocksController extends AdmController
{
    public function common_blocks($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            CommonBlocksConfig::class
        );
    }
}