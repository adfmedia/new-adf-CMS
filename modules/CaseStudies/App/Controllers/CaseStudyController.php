<?php

namespace Modules\CaseStudies\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use App\Models\Url;
use Illuminate\Database\Eloquent\Model;
use Modules\CaseStudies\Models\CaseStudy;
use Modules\CaseStudies\Models\CaseStudyCategory;
use Modules\Pages\Models\Page;

class CaseStudyController extends Controller
{
    public function index(Page $ListingPage)
    {
        if (request()->query('load-more')) {
            return $this->loadMore($ListingPage);
        }

        $Articles = $this->getListingArticles(0, $ListingPage);
        return view(
            'case-studies.listing',
            [
                'Page'          => $ListingPage,
                'categories'    => $this->getSidebarCategories(),
                'Articles'      => $Articles,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($ListingPage->url),
                'rel_links'     => RouteHelper::getPaginationRelLinks($Articles, $ListingPage),
                'load_more'     => $ListingPage->url . '?load-more=1'
            ]
        );
    }

    public function category(CaseStudyCategory $Category)
    {
        if (request()->query('load-more')) {
            return $this->loadMore($Category);
        }

        $Articles = $this->getListingArticles($Category->case_study_category_id, $Category);
        return view(
            'case-studies.listing',
            [
                'Page'          => $Category,
                'categories'    => $this->getSidebarCategories(),
                'Articles'      => $Articles,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Category->url),
                'rel_links'     => RouteHelper::getPaginationRelLinks($Articles, $Category),
                'load_more'     => $Category->url . '?load-more=1'
            ]
        );
    }

    public function article(CaseStudy $CaseStudy)
    {
        return view(
            'case-studies.article',
            [
                'Page'          => $CaseStudy,
                'categories'    => $this->getSidebarCategories(),
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($CaseStudy->url)
            ]
        );
    }

    protected function loadMore(Page|CaseStudyCategory $Page)
    {
        $category_id = $Page instanceof CaseStudyCategory ? $Page->case_study_category_id : 0;
        $Articles = $this->getListingArticles($category_id, $Page);

        return response()->json([
            'listings'  => view('case-studies.partials.ajax-tiles', ['Articles' => $Articles])->render(),
            'has_more'  => $Articles->hasMorePages()
        ]);
    }

    protected function getSidebarCategories(): array
    {
        $categories = [];

        $ListingPage = Url::getSystemPage('case_studies', 'index');
        if ($ListingPage) {
            $categories[] = [
                'url'   => $ListingPage->url,
                'title' => 'All case studies'
            ];
        }

        foreach (CaseStudyCategory::orderBy('title')->with('articles')->get() as $Category) {
            if ($Category->articles->isEmpty()) {
                continue;
            }

            $categories[] = [
                'url'   => $Category->url,
                'title' => $Category->title
            ];
        }

        return $categories;
    }

    protected function getListingArticles(int $category_id, Model $Page)
    {
        $ArticlesQry = CaseStudy::distinct();
        if ($category_id) {
            $ArticlesQry->inCategory($category_id);
        }

        $Paginated = $ArticlesQry->orderBy('date', 'desc')->paginate(12);
        $Paginated->setPath($Page->url);
        $Paginated->appends(request()->except('page'));

        return $Paginated;
    }
}