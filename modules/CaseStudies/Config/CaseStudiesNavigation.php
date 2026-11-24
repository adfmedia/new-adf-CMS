<?php

namespace Modules\CaseStudies\Config;

use Adm\Interfaces\NavigationInterface;

class CaseStudiesNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Case studies',
                'url'       => route('adm.route', 'case-studies'),
                'position'  => 30,
                'sub'       => [
                    [
                        'title'     => 'Articles',
                        'url'       => route('adm.route', 'case-studies'),
                        'position'  => 0
                    ],
                    [
                        'title'     => 'Categories',
                        'url'       => route('adm.route', 'case-study-categories'),
                        'position'  => 10
                    ]
                ]
            ]
        ];
    }
}