<?php

namespace Adm\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use ReflectionClass;

class RouteController extends AdmController
{
    /**
     * Array containing 'protected' controller names
     * that shouldn't be available through the routing
     * path
     * 
     * @var array
     */
    protected $protected_adm_controllers = [
        'AdmController',
        'AuthController',
        'DashboardController',
        'RouteController'
    ];

    /**
     * Handle CMS routing by finding the appropriate controller &
     * method
     * 
     * @param ?string $section
     * @param ?string $method
     * @param ?int $id
     * @return mixed
     */
    public function index(?string $section = null, ?string $method = null, ?int $id = null): mixed
    {
        // Handle CMS Dashboard
        if (is_null($section)) {
            $Ctrl = new DashboardController();
            return $Ctrl->index();
        }

        $section_method = Str::snake(Str::replace('-', '_', $section));
        $Ctrl = $this->getControllerInstance($section_method);
        return $Ctrl->$section_method($method, $id);
    }

    /**
     * Handle CMS preview routing
     * 
     * @param ?string $section
     * @param ?int $id
     * @return mixed
     */
    public function preview(?string $section = null, ?int $id = null): mixed
    {
        return $this->index($section, 'preview', $id);
    }

    /**
     * Return a controller instance for the controller
     * class matching the provided section
     * 
     * @param string $section_method
     * @return \Adm\Http\Controllers\AdmController
     * @throws \Exception
     */
    protected function getControllerInstance(string $section_method): AdmController
    {
        // First of all, check the adm directory
        $adm_dir = base_path('adm/Http/Controllers/');
        $adm_controllers = glob($adm_dir . '*');
        foreach ($adm_controllers as $controller_path) {
            $cname = $this->checkPathForController($adm_dir, $controller_path, 'Adm\Http\Controllers', $section_method);
            if (!is_null($cname)) { 
                return new $cname(); 
            }
        }

        // Otherwise, check modules...
        $modules_with_adm = array_filter(config('modules.available'), function ($module) {
            return is_dir(base_path('modules/' . $module . '/Adm/Controllers'));
        });
        foreach ($modules_with_adm as $module) {
            $module_dir = base_path('modules/' . $module . '/Adm/Controllers/');
            $module_controllers = glob($module_dir . '*');
            foreach ($module_controllers as $controller_path) {
                $cname = $this->checkPathForController(
                    $module_dir, 
                    $controller_path, 
                    'Modules\\' . $module . '\Adm\Controllers', 
                    $section_method
                );
                if (!is_null($cname)) {
                    return new $cname();
                }
            }
        }

        // Finally, if no controller found, throw error
        throw new Exception('Section ' . $section_method . ' not found in system controllers.');
    }

    /**
     * Checks the provided directory path & namespace
     * for a controller that has the provided section method
     * 
     * @param string $dir
     * @param string $path
     * @param string $namespace
     * @param string $section_method
     * @return ?\Adm\Http\Controllers\AdmController
     */
    protected function checkPathForController(string $dir, string $path, string $namespace, string $section_method): ?AdmController
    {
        // Skip subdirectories
        if (is_dir($path)) { return null; }

        // Convert file path to controller name
        $cname = Str::replace($dir, '', $path);
        $cname = Str::replace('.php', '', $cname);

        // Skip any 'protected' controllers
        if (in_array($cname, $this->protected_adm_controllers)) { return null; }

        if ($this->checkControllerForMethod($namespace . '\\' . $cname, $section_method)) {
            $cname = $namespace . '\\' . $cname;
            return new $cname();
        }

        return null;
    }

    /**
     * Determine if the provided controller class has the
     * provided method, which is public
     * 
     * @param string $controller_class
     * @param string $method
     * @return bool
     */
    protected function checkControllerForMethod(string $controller_class, string $method): bool
    {
        $RefClass = new ReflectionClass($controller_class);
        if (!$RefClass->hasMethod($method)) {
            return false;
        }

        $RefMethod = $RefClass->getMethod($method);
        return $RefMethod->isPublic();
    }
}