<?php

namespace Modules\Faqs\Config;

use Adm\Interfaces\NavigationInterface;

class FaqsNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'FAQs',
                'url'       => route('adm.route', 'faqs'),
                'position'  => 50,
                'sub'       => [
                    [
                        'title'     => 'FAQs',
                        'url'       => route('adm.route', 'faqs'),
                        'position'  => 0
                    ],
                    [
                        'title'     => 'Categories',
                        'url'       => route('adm.route', 'faq-categories'),
                        'position'  => 10
                    ]
                ]
            ]
        ];
    }
}