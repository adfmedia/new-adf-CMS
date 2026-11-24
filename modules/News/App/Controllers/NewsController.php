<?php

namespace Modules\News\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use App\Models\Url;
use Illuminate\Database\Eloquent\Model;
use Modules\News\Models\NewsArticle;
use Modules\News\Models\NewsCategory;
use Modules\Pages\Models\Page;

class NewsController extends Controller
{
    public function index(Page $ListingPage)
    {
        if (request()->query('load-more')) {
            return $this->loadMore($ListingPage);
        }

        $Articles = $this->getListingArticles(0, $ListingPage);
        return view(
            'news::listing',
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

    public function category(NewsCategory $Category)
    {
        if (request()->query('load-more')) {
            return $this->loadMore($Category);
        }

        $Articles = $this->getListingArticles($Category->news_category_id, $Category);
        return view(
            'news::listing',
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

    public function article(NewsArticle $Article)
    {
        return view(
            'news::article',
            [
                'Page'          => $Article,
                'categories'    => $this->getSidebarCategories(),
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Article->url)
            ]
        );
    }

    protected function loadMore(Page|NewsCategory $Page)
    {
        $category_id = $Page instanceof NewsCategory ? $Page->news_category_id : 0;
        $Articles = $this->getListingArticles($category_id, $Page);

        return response()->json([
            'listings'  => view('news::partials.ajax-tiles', ['Articles' => $Articles])->render(),
            'has_more'  => $Articles->hasMorePages()
        ]);
    }

    protected function getSidebarCategories(): array
    {
        $categories = [];

        $ListingPage = Url::getSystemPage('news', 'index');
        if ($ListingPage) {
            $categories[] = [
                'url'   => $ListingPage->url,
                'title' => 'All News'
            ];
        }

        foreach (NewsCategory::orderBy('title')->with('articles')->get() as $Category) {
            if ($Category->articles->isEmpty()) { continue; }

            $categories[] = [
                'url'   => $Category->url,
                'title' => $Category->title
            ];
        }

        return $categories;
    }

    protected function getListingArticles(int $category_id, Model $Page)
    {
        $ArticlesQry = NewsArticle::distinct();
        if ($category_id) {
            $ArticlesQry->inCategory($category_id);
        }

        $Paginated = $ArticlesQry->orderBy('article_date', 'desc')->paginate(12);
        $Paginated->setPath($Page->url);
        $Paginated->appends(request()->except('page'));

        return $Paginated;
    }
}