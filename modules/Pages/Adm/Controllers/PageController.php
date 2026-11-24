<?php

namespace Modules\Pages\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\Pages\Config\BlockOnlyPageConfig;
use Modules\Pages\Config\PagesConfig;

class PageController extends AdmController
{
    public function pages($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            PagesConfig::class
        );
    }

    public function block_only_page($method, $id)
    {
        if (!in_array($method, ['edit', 'update'])) {
            return response()->redirectTo(route('adm.route', [BlockOnlyPageConfig::$url_slug, 'edit', $id]));
        }
        return $this->Crud->boot($method, $id, BlockOnlyPageConfig::class);
    }
}