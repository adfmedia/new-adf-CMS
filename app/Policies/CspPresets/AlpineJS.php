<?php

namespace App\Policies\CspPresets;

use App\Helpers\RouteHelper;
use Spatie\Csp\Directive;
use Spatie\Csp\Keyword;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;

class AlpineJS implements Preset
{
    public function configure(Policy $policy): void
    {
        if (config('app.alpinejs_fe_enabled') || RouteHelper::isCmsRoute()) {
            $policy
                ->add(Directive::SCRIPT, Keyword::UNSAFE_EVAL);
        }
    }
}