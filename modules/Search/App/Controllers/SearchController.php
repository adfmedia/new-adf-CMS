<?php

namespace Modules\Search\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use Modules\Pages\Models\Page;
use Modules\Search\Library\SiteSearch;

class SearchController extends Controller
{
    public function index(Page $SearchPage)
    {
        $Search = new SiteSearch();
        $search_term = request()->query('search');
        $Results = !empty($search_term) ? $Search->search($search_term) : false;
        if ($Results) {
            $Results->setPath($SearchPage->url);
            $Results->appends(request()->except('page'));
        }

        return view(
            'search::results',
            [
                'Page'          => $SearchPage,
                'search_term'   => $search_term,
                'Results'       => $Results,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($SearchPage->url)
            ]
        );
    }
}