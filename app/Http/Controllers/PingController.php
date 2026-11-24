<?php

namespace App\Http\Controllers;

use App\Helpers\ClassFinder;
use App\Helpers\TrackingDataHelper;
use App\Models\Url;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PingController extends Controller
{
    /**
     * Return a JSON response containing the current session's
     * CSRF token
     * 
     * @return JsonResponse
     */
    public function token(): JsonResponse
    {
        $session = app('session');
        if (!$session) {
            return response()->json([]);
        }

        return response()->json(['token' => $session->token()]);
    }

    /**
     * Perform optional tracking data retrieval & storage
     * 
     * @return Response
     */
    public function form_tracking(): Response
    {
        $TrackingDataHelper = new TrackingDataHelper();
        $TrackingDataHelper->sourceAndSetData();
        
        return response('');
    }

    /**
     * Return a JSON response containing the URL for the 'Edit
     * this page' button
     * 
     * @return JsonResponse
     */
    public function cms_url(): JsonResponse
    {
        $json = [];
        if (!Auth::guard('adm')->user()) {
            return response()->json($json);
        }

        try {
            $url_id = request()->input('url');
            $Url = Url::find($url_id);
            $Page = $Url->urlable;

            $slug = $Page->cms_url_slug;
            if (!$slug) {
                $ConfigClass = ClassFinder::findConfigClassForModel($Url->urlable_type);
                $slug = $ConfigClass->getStaticPropertyValue('url_slug');
            }
            
            $json['url'] = route('adm.route', [$slug, 'edit', $Url->urlable_id]);
        } catch (Exception $e) {}

        return response()->json($json);
    }
}