<?php

namespace Adm\Http\Controllers;

use Adm\Crud\CrudField;
use Adm\Library\Cloudflare\CFCacheInterface;
use Adm\Library\LinkSearch;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AjaxToolsController extends AdmController
{
    /**
     * Common function loader for common AJAX tool calls,
     * calling the requested function if available,
     * throwing a 404 error if not
     * 
     * @param string $requested_function
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function fn_loader(string $requested_function): mixed
    {
        if (method_exists($this, $requested_function)) {
            return $this->$requested_function();
        }

        throw new NotFoundHttpException();
    }

    /**
     * Returns a JSON response containing all internal links
     * matching the requested search term
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    protected function link_search(): JsonResponse
    {
        $search_term = request()->input('search');
        return response()->json([
            'links' => $search_term ? LinkSearch::search_links($search_term) : []
        ]);
    }

    /**
     * Calls the requested method on the requested Crud
     * Field class, or throws a 404 if unsuccessful
     * 
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function field_method(): mixed
    {
        $field_type = request()->input('field');
        $method = request()->input('method');

        $Field = new CrudField($this->Crud, $field_type);
        $resp = $Field->{$method}();
        if (!$resp) {
            throw new NotFoundHttpException();
        }

        return $resp;
    }

    /**
     * Calls the requested method on the CMS Menu
     * controller, or throws a 404 if not found
     * 
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function menu_method()
    {
        $method = request()->input('method');
        $MenuController = new MenuController();
        if (!method_exists($MenuController, $method)) {
            throw new NotFoundHttpException();
        }

        return $MenuController->{$method}();
    }

    /**
     * Purges the Cloudflare cache
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function cf_purge(): JsonResponse
    {
        $CF = new CFCacheInterface();
        $CF->purgeAll();

        return response()->json([
            'success'   => true
        ]);
    }

    /**
     * Toggles Cloudflare development mode on or off
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function cf_dev_mode_switch(): JsonResponse
    {
        $CF = new CFCacheInterface();
        $CF->switchDevelopmentMode();
        return response()->json([
            'success'   => true,
            'status'    => $CF->getDevelopmentMode()
        ]);
    }
}