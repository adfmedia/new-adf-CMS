<?php

namespace App\Http\Controllers;

use App\Helpers\LanguageHelper;
use App\Models\Redirect;
use App\Models\Scopes\LanguageScope;
use App\Models\Url;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteController extends BaseController
{
    /**
     * Performs internal controller & method routing for the provided
     * URL path
     * 
     * @param string $url_path
     * @return Response|JsonResponse|RedirectResponse
     * @throws NotFoundHttpException
     */
    public function route(string $url_path = ''): Response|JsonResponse|RedirectResponse
    {
        $Url = Url::where('url', $url_path)->first();
        if (!$Url) {
            // If we don't have a domain/subdomain based match
            // check globally & override the current language
            $Url = Url::withoutGlobalScope(LanguageScope::class)
                ->where('url', $url_path)
                ->first();
            if ($Url) {
                LanguageHelper::changeLanguage($Url->language);
            }
        }
        
        if (!$Url) {
            $Redirected = $this->checkNotFoundRedirects($url_path);
            if ($Redirected) {
                return $Redirected;
            }

            $this->throwNotFoundError();
        }

        $LinkedResource = null;
        try {
            $LinkedResource = $Url->urlable;
        } catch (Exception $e) {
            Log::debug('URL Linked Resource not found for ' . $Url->url);
            Log::debug($e);
        }

        if (!$LinkedResource) {
            $this->throwNotFoundError();
        }

        if ($Url->system_page) {
            $Response = $this->handleSystemPage($Url, $LinkedResource);
        } else {
            $Response = $this->handleResource($LinkedResource);
        }

        return $this->returnResponse($Response, $Url, $LinkedResource);
    }

    /**
     * Handles an AJAX request, routed through the
     * provided controller & method
     * 
     * @param string $controller
     * @param string $method
     * @return Response|JsonResponse|View
     * @throws NotFoundHttpException
     */
    public function ajax(string $controller, string $method): Response|JsonResponse|View
    {
        $controller_class = $this->findControllerForMethod($controller, $method);
        if (!$controller_class || !method_exists($controller_class, $method)) {
            $this->throwNotFoundError();
        }

        $Ctrl = new $controller_class();
        return $Ctrl->$method();
    }

    /**
     * Handles a CMS preview link, serving the previewable page
     * 
     * @param Url $Url
     * @param Model $LinkedResource
     * @return Response
     * @throws NotFoundHttpException
     */
    public function cmsPreview($Url, $LinkedResource): Response
    {
        if ($Url->system_page) {
            $Response = $this->handleSystemPage($Url, $LinkedResource);
        } else {
            $Response = $this->handleResource($LinkedResource);
        }

        return $this->returnResponse($Response, $Url, $LinkedResource);
    }

    /**
     * Handles a system page's route binding, calling the appropriate
     * controller & method for it
     * 
     * @param Url $Url
     * @param Model $LinkedResource
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function handleSystemPage(Url $Url, Model $LinkedResource): mixed
    {
        $split = explode('&', $Url->route);
        $controller_name = $method = null;
        foreach ($split as $part) {
            $part_parts = explode('=', $part);
            if ($part_parts[0] == 'controller') {
                $controller_name = $part_parts[1];
            }
            elseif ($part_parts[0] == 'method') {
                $method = $part_parts[1];
            }
        }
        if (is_null($controller_name) || is_null($method)) {
            $this->throwNotFoundError();
        }

        $controller_class = $this->findControllerForMethod($controller_name, $method);
        if (!$controller_class) {
            Log::debug('No controller found. Controller: ' . $controller_class . ' - Method: ' . $method);
            $this->throwNotFoundError();
        }

        $Ctrl = new $controller_class();
        return $Ctrl->$method($LinkedResource);
    }

    /**
     * Handles the linked resources route binding, calling the appropriate
     * controller & method for it
     * 
     * @param Model $LinkedResource
     * @return mixed
     * @throws NotFoundHttpException
     */
    protected function handleResource(Model $LinkedResource): mixed
    {
        if (property_exists($LinkedResource, 'route_binding')) {
            $controller_class = $LinkedResource->route_binding['controller'];
            $method = isset($LinkedResource->route_binding['method']) ? $LinkedResource->route_binding['method'] : 'index';
        } else {
            $controller_class = $this->findControllerForMethod($LinkedResource->getTable(), 'index');
            $method = 'index';
        }

        if (!$controller_class || !class_exists($controller_class)) {
            Log::debug('Controller ' . $controller_class . ' not found.');
            $this->throwNotFoundError();
        }
        if (!method_exists($controller_class, $method)) {
            Log::debug('Controller ' . $controller_class . ' does not have method ' . $method);
            $this->throwNotFoundError();
        }

        $Ctrl = new $controller_class();
        return $Ctrl->$method($LinkedResource);
    }

    /**
     * Find the controller within the system that matches the provided
     * name & has the provided method
     * 
     * @param string $controller_name
     * @param string $method
     * @return ?string
     */
    protected function findControllerForMethod(string $controller_name, string $method): ?string
    {
        // Format controller name
        $controller_name = Str::studly(Str::singular($controller_name) . '_controller');

        // Check App namespace
        $app_class = 'App\Http\Controllers\\' . $controller_name;
        if (class_exists($app_class) && method_exists($app_class, $method)) {
            return $app_class;
        }

        // Check modules...
        $modules_with_app = array_filter(config('modules.available'), function ($module) {
            return is_dir(base_path('modules/' . $module . '/App/Controllers'));
        });
        foreach ($modules_with_app as $module) {
            $module_class = 'Modules\\' . $module . '\App\Controllers\\' . $controller_name;
            if (class_exists($module_class) && method_exists($module_class, $method)) {
                return $module_class;
            }
        }

        // Finally, if not found, return null
        return null;
    }

    /**
     * Returns a commonly formatted response object for the provided
     * response, URL & resource linked to the URL
     * 
     * @param mixed $Response
     * @param Url $Url
     * @param Model $LinkedResource
     * @return Response|JsonResponse|RedirectResponse
     */
    protected function returnResponse(mixed $Response, Url $Url, Model $LinkedResource): Response|JsonResponse|RedirectResponse
    {
        if ($Response instanceof JsonResponse) { return $Response; }
        if ($Response instanceof RedirectResponse) { return $Response; }
        if ($Response instanceof View) {
            $Response = $this->renderPage($Response, $Url, $LinkedResource);
        }

        return response($Response);
    }

    /**
     * 'Renders' a page, including the current URL object with the
     * provided view
     * 
     * @param View $View
     * @param Url $Url
     * @param Model $LinkedResource
     * @return View
     */
    protected function renderPage(View $View, Url $Url, Model $LinkedResource): View
    {
        return $View->with('UrlObject', $Url);
    }

    /**
     * Throws a NotFoundHttpException
     * 
     * @throws NotFoundHttpException
     */
    protected function throwNotFoundError()
    {
        throw new NotFoundHttpException();
    }

    /**
     * Check if a redirect exists for the provided
     * URL path to use when the page is not found
     * 
     * @param string $url_path
     * @return ?RedirectResponse
     */
    protected function checkNotFoundRedirects(string $url_path = '/'): ?RedirectResponse
    {
        $Redirected = Redirect::afterUrlLookup()->search($url_path)->first();
        if (!$Redirected) {
            $Redirected = Redirect::afterUrlLookup()->wildcardSearch($url_path)->first();
        }

        if ($Redirected) {
            return response()->redirectTo(
                Redirect::parseRedirectTo($url_path, $Redirected),
                $Redirected->redirect_type
            );
        }

        return null;
    }
}