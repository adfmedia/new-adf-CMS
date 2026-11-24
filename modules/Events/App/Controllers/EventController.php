<?php

namespace Modules\Events\App\Controllers;

use App\Helpers\RouteHelper;
use App\Http\Controllers\Controller;
use App\Models\Url;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Events\Models\Event;
use Modules\Events\Models\EventCategory;
use Modules\Pages\Models\Page;

class EventController extends Controller
{
    const PAGINATION_LIMIT  = 12;

    public function index(Page $Page)
    {
        return view(
            'events.listing',
            [
                'Page'          => $Page,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url),

                'Events'        => $this->getEventListings($Page),
                'categories'    => $this->getSidebarCategories($Page),
                'months'        => $this->getSidebarMonths(),
            ]
        );
    }

    public function category(EventCategory $Page)
    {
        return view(
            'events.listing',
            [
                'Page'          => $Page,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url),

                'Events'        => $this->getEventListings($Page),
                'categories'    => $this->getSidebarCategories($Page),
                'months'        => $this->getSidebarMonths(),
            ]
        );
    }

    public function event(Event $Page)
    {
        return view(
            'events.event',
            [
                'Page'          => $Page,
                'breadcrumbs'   => RouteHelper::generateBreadcrumbs($Page->url),

                'categories'    => $this->getSidebarCategories($Page),
                'months'        => $this->getSidebarMonths(),
            ]
        );
    }

    protected function getEventListings(Model $Page)
    {
        if ($Page instanceof EventCategory) {
            $Events = $Page->events();
        } else {
            $Events = Event::distinct();
        }

        if (request()->query('month')) {
            $Events->matchingMonthYear(request()->query('month'));
        }

        if (request()->query('archive')) {
            $Events = $Events->dateOrder(false)->paginate(self::PAGINATION_LIMIT);
        } else {
            $Events = $Events->dateOrder()->paginate(self::PAGINATION_LIMIT);
        }

        $Events->appends(request()->except('page'));
        $Events->setPath($Page->url);
        return $Events;
    }

    protected function getSidebarCategories(Model $Page): array
    {
        $categories = [];
        $ListingPage = Url::getSystemPage('event', 'index');
        if ($ListingPage) {
            $categories[] = [
                'title'     => 'All events',
                'url'       => $ListingPage->url,
                'active'    => $Page->page_id == $ListingPage->page_id && !request()->query('month')
            ];
        }

        foreach (EventCategory::whereHas('events')->priorityOrder()->get() as $Category) {
            $categories[] = [
                'title'     => $Category->title,
                'url'       => $Category->url,
                'active'    => $Category->event_category_id == $Page->event_category_id
            ];
        }

        return $categories;
    }

    protected function getSidebarMonths(): array
    {
        $ListingPage = Url::getSystemPage('event', 'index');
        if (!$ListingPage) { 
            return []; 
        }

        $months = [];
        foreach (Event::getUpcomingMonths() as $MonthYear) {
            $ym_format = $MonthYear->year . '-' . $MonthYear->month;
            $months[] = [
                'title'     => Carbon::createFromFormat("Y-m", $ym_format)->format("F Y"),
                'url'       => $ListingPage->url . '?month=' . $ym_format,
                'active'    => $ym_format == request()->query('month')
            ];
        }

        return $months;
    }
}