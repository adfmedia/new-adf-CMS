<?php

namespace App\Policies\CspPresets;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class YouTube implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::FRAME, ['https://www.youtube.com', 'https://www.youtube-nocookie.com']);
    }
}
