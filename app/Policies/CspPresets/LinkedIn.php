<?php

namespace App\Policies\CspPresets;

use Spatie\Csp\Directive;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class LinkedIn implements Preset
{
    public function configure(Policy $policy): void
    {
        $policy
            ->add(Directive::CONNECT, 'https://*.linkedin.com');
    }
}
