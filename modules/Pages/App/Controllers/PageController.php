<?php

namespace Modules\Pages\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use Modules\Pages\Models\Page;

class PageController extends Controller
{
    public function index(Page $Page)
    {
        return view(
            'pages.page',
            [
                'Page'          => $Page,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url)
            ]
        );
    }

    public function privacy_policy(Page $Page)
    {
        return $this->index($Page);
    }
}