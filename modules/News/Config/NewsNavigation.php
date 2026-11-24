<?php

namespace Modules\News\Config;

use Adm\Interfaces\NavigationInterface;

class NewsNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'News',
                'url'       => route('adm.route', 'news-articles'),
                'position'  => 20,
                'sub'       => [
                    [
                        'title'     => 'Articles',
                        'url'       => route('adm.route', 'news-articles'),
                        'position'  => 0
                    ],
                    [
                        'title'     => 'Categories',
                        'url'       => route('adm.route', 'news-categories'),
                        'position'  => 10
                    ],
                    [
                        'title'     => 'Authors',
                        'url'       => route('adm.route', 'news-authors'),
                        'position'  => 20
                    ]
                ]
            ]
        ];
    }
}