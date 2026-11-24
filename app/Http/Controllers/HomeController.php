<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Modules\Pages\Models\Page;

class HomeController extends Controller
{
    /**
     * Serve the website home page
     * 
     * @param Page $Page
     * @return View
     */
    public function index(Page $Page): View
    {
        return view(
            'pages::home',
            [
                'Page'  => $Page
            ]
        );
    }
}