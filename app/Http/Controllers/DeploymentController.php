<?php

namespace App\Http\Controllers;

use App\Providers\ModuleMigrationServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DeploymentController extends Controller
{
    /**
     * Perform any pre-deployment actions
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function preHook(Request $request): JsonResponse
    {
        if (!self::isValidRequest($request)) {
            throw new AuthorizationException();
        }
        if (self::isWebhookTest($request)) {
            return response()->json("");
        }

        Artisan::call('down');
        return response()->json("success");
    }

    /**
     * Perform any post-deployment actions
     * 
     * @param Request $request
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function postHook(Request $request): JsonResponse
    {
        if (!self::isValidRequest($request)) {
            throw new AuthorizationException();
        }
        if (self::isWebhookTest($request)) {
            return response()->json("");
        }

        self::runDBMigrations();
        //Artisan::call('images:resize');
        Artisan::call('cache:clear');
        Artisan::call('up');
        return response()->json("success");
    }

    /**
     * Determine if the request is a valid deployment webhook
     * 
     * @param Request $request
     * @return bool
     */
    protected static function isValidRequest(Request $request): bool
    {
        return $request->has('webhook-key') && $request->query('webhook-key') == env('BEANSTALK_WEBHOOK_KEY');
    }

    /**
     * Determine if the request is a webhook test or not
     * 
     * @param Request $request
     * @return bool
     */
    protected static function isWebhookTest(Request $request): bool
    {
        return !$request->isMethod('post')
            || ($request->has('comment') && $request->input('comment') == 'WebHook Test');
    }

    /**
     * Run any new database migrations, both in the default
     * directory & any module directories
     * 
     * @return void
     */
    protected static function runDBMigrations(): void
    {
        // Firstly, run migrations in default path
        Artisan::call('migrate', [
            '--force'   => true
        ]);

        // Then, run migrations in module paths
        foreach (ModuleMigrationServiceProvider::getModuleMigrationPaths() as $mod_path) {
            Artisan::call('migrate', [
                '--force'   => true,
                '--path'    => $mod_path
            ]);
        }
    }
}