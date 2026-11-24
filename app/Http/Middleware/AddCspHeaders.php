<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Override;
use Spatie\Csp\AddCspHeaders as CspAddCspHeaders;

class AddCspHeaders extends CspAddCspHeaders
{
    #[Override]
    public function handle(Request $request, Closure $next, ?string $customPreset = null)
    {
        if (config('csp.use_meta_tag')) {
            return $next($request);
        }

        return parent::handle($request, $next, $customPreset);
    }
}