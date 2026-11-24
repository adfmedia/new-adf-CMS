<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Str;
use ReflectionClass;

class ClassFinder
{
    /**
     * Get all configuration classes within the application
     * 
     * @return array
     */
    public static function getConfigClasses(): array
    {
        $classes = [];
        foreach (self::getClassesFromDirectory(base_path('adm/Config'), 'Adm\Config') as $CoreClass) {
            $classes[] = $CoreClass;
        }
        foreach (self::getModuleClassesInDirectory('Config', 'Config') as $ModuleClass) {
            $classes[] = $ModuleClass;
        }

        return array_filter($classes, function ($Class) {
            return !($Class instanceof \Adm\Interfaces\NavigationInterface);
        });
    }

    /**
     * Find the configuration class matching the provided model class
     * 
     * @param string $model_class
     * @return ReflectionClass|bool
     */
    public static function findConfigClassForModel(string $model_class): ReflectionClass|bool
    {
        foreach (self::getConfigClasses() as $ConfigClass) {
            $RefClass = new ReflectionClass($ConfigClass);
            if ($RefClass->hasProperty('skip_class_finder') && $RefClass->getStaticPropertyValue('skip_class_finder')) { continue; }
            
            if ($RefClass->hasProperty('class') && $RefClass->getStaticPropertyValue('class') == $model_class) {
                return $RefClass;
            }
        }

        return false;
    }

    /**
     * Get all CMS navigation configuration classes in the application
     * 
     * @return array
     */
    public static function getAdminNavigationClasses(): array
    {
        $classes = [];

        // Load classes from adm/Config
        foreach (self::getClassesFromDirectory(base_path('adm/Config'), 'Adm\Config') as $CoreClass) {
            $classes[] = $CoreClass;
        }

        // Load module classes
        foreach (self::getModuleClassesInDirectory('Config', 'Config') as $ModuleClass) {
            $classes[] = $ModuleClass;
        }

        return array_filter($classes, function ($Class) {
            return $Class instanceof \Adm\Interfaces\NavigationInterface;
        });
    }

    /**
     * Get all page builder block configuration classes in the application
     * 
     * @return array
     */
    public static function getPageBuilderBlockClasses(): array
    {
        $classes = [];
        foreach (self::getModuleClassesInDirectory('Blocks', 'Blocks') as $BlockClass) {
            $BlockRef = new ReflectionClass($BlockClass);
            if ($BlockRef->isSubclassOf(\App\Blocks\AbstractBlock::class)) {
                $classes[] = $BlockRef;
            }
        }

        return $classes;
    }

    /**
     * Get all classes within the provided $directory that match the provided $namespace
     * 
     * @param string $directory
     * @param string $namespace
     * @return array
     */
    public static function getClassesFromDirectory(string $directory, string $namespace): array
    {
        $classes = [];

        foreach (glob($directory . '/*') as $file_path) {
            $class_file_name = Str::replace(
                [$directory . '/', '.php'],
                ['', ''],
                $file_path
            );
            $class_name = $namespace . '\\' . $class_file_name;

            if (!class_exists($class_name)) {
                throw new Exception('Class ' . $class_name . ' not found.');
            }

            $classes[] = new $class_name();
        }

        return $classes;
    }

    /**
     * Get all module classes within the provided $directory that match the provided $namespace
     * 
     * @param string $directory
     * @param string $namespace
     * @return array
     */
    protected static function getModuleClassesInDirectory(string $directory, string $namespace): array
    {
        $modules = self::getModulesWithSubdirectory($directory);

        $classes = [];
        foreach ($modules as $module) {
            $path = 'modules/' . $module . '/' . $directory;
            $ns = 'Modules\\' . $module . '\\' . $namespace;
            foreach (self::getClassesFromDirectory(base_path($path), $ns) as $ModuleClass) {
                $classes[] = $ModuleClass;
            }
        }

        return $classes;
    }

    /**
     * Get all available modules that include the provided $subdirectory
     * 
     * @param string $subdirectory
     * @return array
     */
    protected static function getModulesWithSubdirectory(string $subdirectory): array
    {
        return array_filter(config('modules.available'), function ($module) use ($subdirectory) {
            return is_dir(base_path('modules/' . $module . '/' . $subdirectory));
        });
    }
}