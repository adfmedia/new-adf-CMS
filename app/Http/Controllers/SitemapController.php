<?php

namespace App\Http\Controllers;

use App\Helpers\ClassFinder;
use App\Helpers\RouteHelper;
use App\Models\Scopes\LanguageScope;
use App\Models\Url;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\News\Models\NewsArticle;
use Modules\Pages\Models\Page;
use XMLWriter;

class SitemapController extends Controller
{
    /**
     * Serve the HTML sitemap
     * 
     * @param Page $Page
     * @return View
     */
    public function html(Page $Page): View
    {
        return view(
            'sitemap.html',
            [
                'Page'          => $Page,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url),
                'groups'        => $this->getHtmlSitemapGroups()
            ]
        );
    }

    /**
     * Serve the XML sitemap
     * 
     * @return Response
     */
    public function xml(): Response
    {
        $IndexableUrls = Url::withoutGlobalScope(LanguageScope::class)
            ->with('language')
            ->indexable()
            ->get()
            ->filter(function ($Url) {
                return $Url->urlable ? true : false;
            }
        );

        $Sitemap = new XMLWriter();
        $Sitemap->openMemory();
        $Sitemap->startDocument("1.0", "UTF-8");
        $Sitemap->startElementNs(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($IndexableUrls as $Url) {
            $Sitemap->startElement('url');
                
            $Sitemap->writeElement('loc', $Url->alternate_href);
            $Sitemap->writeElement('lastmod', $Url->urlable->updated_at->format("Y-m-d"));

            if (env('SITEMAP_CHANGEFREQ')) {
                $Sitemap->writeElement('changefreq', 'weekly');
            }
            if (env('SITEMAP_PRIORITY')) {
                $Sitemap->writeElement('priority', '1.0');
            }

            $Sitemap->endElement();
        }

        $Sitemap->endElement();
        $Sitemap->endDocument();

        return response($Sitemap->outputMemory())
            ->header('Content-type', 'text/xml');
    }

    /**
     * Serve the XML news sitemap
     * 
     * @return Response
     */
    public function news(): Response
    {
        $from_ts = strtotime("-2 days");
        $Articles = NewsArticle::whereDate('article_date', '>=', date("Y-m-d", $from_ts))->get();

        $Sitemap = new XMLWriter();
        $Sitemap->openMemory();
        $Sitemap->startDocument("1.0", "UTF-8");
        $Sitemap->startElementNs(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $Sitemap->writeAttributeNs('xmlns', 'news', null, 'http://www.google.com/schemas/sitemap-news/0.9');

        foreach ($Articles as $Article) {
            $Sitemap->startElement('url');

            $Sitemap->writeElement('loc', $Article->url);
            $Sitemap->startElement('news:news');
            $Sitemap->startElement('news:publication');
            $Sitemap->writeElement('news:name', config('app.name'));
            $Sitemap->writeElement('news:language', 'en');
            $Sitemap->endElement(); // news:publication
            $Sitemap->writeElement('news:publication_date', $Article->article_date_carbon->format("Y-m-d"));
            $Sitemap->writeElement('news:title', $Article->title);
            $Sitemap->endElement(); // news:news

            $Sitemap->endElement(); // url
        }

        $Sitemap->endElement();
        $Sitemap->endDocument();

        return response($Sitemap->outputMemory())
            ->header('Content-type', 'text/xml');
    }

    /**
     * Get groups of pages to render on the HTML sitemap
     * 
     * @return array
     */
    protected function getHtmlSitemapGroups(): array
    {
        $model_title_map    = [];
        $sitemap_groups     = [];

        // Get all indexed URLs aside from home & sitemap
        $IndexableUrls = Url::indexable()->where(function (Builder $subquery) {
            $subquery->where([
                ['route', '!=', 'controller=home&method=index'],
                ['route', '!=', 'controller=sitemap&method=html'],
            ])
            ->orWhereNull('route');
        })->get();

        foreach ($IndexableUrls as $Url) {
            if (!$Url->urlable) { continue; }

            $model_class = $Url->urlable_type;
            if (!isset($model_title_map[$model_class])) {
                $ConfigReflection = ClassFinder::findConfigClassForModel($model_class);
                if (!$ConfigReflection) { continue; }

                $model_title_map[$model_class] = $ConfigReflection->getStaticPropertyValue('title');
            }
            $model_group = $model_title_map[$model_class];

            if (!isset($sitemap_groups[$model_group])) {
                $sitemap_groups[$model_group] = new Collection();
            }

            $sitemap_groups[$model_group]->push($Url->urlable);
        }

        foreach ($sitemap_groups as $group_title => $Pages) {
            $sitemap_groups[$group_title] = $Pages->sortBy('title');
        }

        return $sitemap_groups;
    }
}