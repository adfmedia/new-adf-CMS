<?php

namespace App\Policies\CspPresets;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class GLightbox implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::CONNECT, ['https://cdn.plyr.io', 'https://noembed.com'])
            ->add(Directive::SCRIPT, ['https://cdn.plyr.io', 'https://www.youtube.com'])
            ->add(Directive::STYLE, 'https://cdn.plyr.io');
    }
}
