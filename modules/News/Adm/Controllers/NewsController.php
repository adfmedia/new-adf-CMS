<?php

namespace Modules\News\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use Modules\News\Config\NewsArticlesConfig;
use Modules\News\Config\NewsAuthorsConfig;
use Modules\News\Config\NewsCategoriesConfig;

class NewsController extends AdmController
{
    public function news_articles($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            NewsArticlesConfig::class
        );
    }

    public function news_categories($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            NewsCategoriesConfig::class
        );
    }

    public function news_authors($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            NewsAuthorsConfig::class
        );
    }
}