<?php

namespace Modules\Testimonials\Config;

use Adm\Interfaces\NavigationInterface;

class TestimonialsNavigation implements NavigationInterface
{
    public function getNavItems(): array
    {
        return [
            [
                'title'     => 'Testimonials',
                'url'       => route('adm.route', 'testimonials'),
                'position'  => 50,
            ]
        ];
    }
}