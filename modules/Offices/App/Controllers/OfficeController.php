<?php

namespace Modules\Offices\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use Modules\Offices\Models\Office;
use Modules\Pages\Models\Page;

class OfficeController extends Controller
{
    public function index(Page $Page)
    {
        return view(
            'offices::listing',
            [
                'Page'          => $Page,
                'Offices'       => Office::priorityOrder()->get(),
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url),
            ]
        );
    }

    public function office(Office $Office)
    {
        return view(
            'offices::office',
            [
                'Page'          => $Office,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Office->url)
            ]
        );
    }
}