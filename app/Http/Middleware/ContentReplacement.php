<?php

namespace App\Http\Middleware;

use App\Helpers\ContentReplaceHelper;
use Closure;
use Illuminate\Http\Request;

class ContentReplacement
{

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!method_exists($response, 'content')) {
            return $response;
        }

        $content = ContentReplaceHelper::replaceContentFromStorage($response->content());
        $response->setContent($content);
        return $response;
    }
}