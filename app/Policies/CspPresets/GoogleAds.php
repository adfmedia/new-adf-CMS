<?php

namespace App\Policies\CspPresets;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class GoogleAds implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::CONNECT, 'https://*.doubleclick.net')
            ->add(Directive::SCRIPT, ['https://*.googleadservices.com', 'https://*.doubleclick.net']);
    }
}
