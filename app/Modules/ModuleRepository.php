<?php

namespace App\Modules;

use App\Models\InstalledModule;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Knows which modules exist on disk and which are enabled.
 *
 * The source of truth is the `modules` table (the registry). Each request
 * reads the enabled list from bootstrap/cache/modules.php, which the
 * ModuleManager rewrites whenever a module changes, so booting the site
 * never needs a database query. If that file is missing it is rebuilt from
 * the table, and if there is no table yet (a fresh install, or the
 * installer running) the list in config/modules.php is used instead.
 */
class ModuleRepository
{
    /**
     * @var array<string, Module>|null
     */
    protected ?array $enabled = null;

    /**
     * Where the enabled list was read from on this request: cache, registry or config
     *
     * @var string|null
     */
    protected ?string $source = null;

    /**
     * Path to the enabled-modules cache file
     *
     * @return string
     */
    public static function cachePath(): string
    {
        return base_path('bootstrap/cache/modules.php');
    }

    /**
     * All enabled modules, keyed by module name
     *
     * @return array<string, Module>
     */
    public function enabled(): array
    {
        if ($this->enabled !== null) {
            return $this->enabled;
        }

        $this->enabled = [];
        foreach ($this->enabledNames() as $name) {
            $path = base_path('modules' . DIRECTORY_SEPARATOR . $name);
            if (is_dir($path)) {
                $this->enabled[$name] = Module::fromPath($name, $path);
            }
        }

        return $this->enabled;
    }

    /**
     * Names of the enabled modules
     *
     * @return array<int, string>
     */
    public function enabledNames(): array
    {
        if ($this->enabled !== null) {
            return array_keys($this->enabled);
        }

        // 1. The cache file written by the ModuleManager
        $cache_path = self::cachePath();
        if (is_file($cache_path)) {
            $cached = require $cache_path;
            if (is_array($cached) && isset($cached['enabled']) && is_array($cached['enabled'])) {
                $this->source = 'cache';
                return $cached['enabled'];
            }
        }

        // 2. The registry table, then write the cache for next time
        $from_registry = $this->readRegistry();
        if ($from_registry !== null) {
            $this->source = 'registry';
            $this->writeCache($from_registry);
            return $from_registry;
        }

        // 3. Before the registry exists: the declared list in config/modules.php
        $this->source = 'config';
        return $this->declaredNames();
    }

    /**
     * Whether a module is enabled
     *
     * @param string $name
     * @return bool
     */
    public function isEnabled(string $name): bool
    {
        return in_array($name, $this->enabledNames(), true);
    }

    /**
     * Find an enabled module by name
     *
     * @param string $name
     * @return Module|null
     */
    public function find(string $name): ?Module
    {
        return $this->enabled()[$name] ?? null;
    }

    /**
     * Load any module on disk by name, enabled or not
     *
     * @param string $name
     * @return Module|null
     */
    public function onDisk(string $name): ?Module
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name)) {
            return null;
        }

        $path = base_path('modules' . DIRECTORY_SEPARATOR . $name);
        return is_dir($path) ? Module::fromPath($name, $path) : null;
    }

    /**
     * All module folders on disk, keyed by name
     *
     * @return array<string, Module>
     */
    public function allOnDisk(): array
    {
        $modules = [];
        foreach (glob(base_path('modules') . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $path) {
            $name = basename($path);
            if (preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $name)) {
                $modules[$name] = Module::fromPath($name, $path);
            }
        }
        ksort($modules);

        return $modules;
    }

    /**
     * Find the enabled module a class belongs to, from its Modules\<Name>\... namespace
     *
     * @param string $class
     * @return Module|null
     */
    public function forClass(string $class): ?Module
    {
        $name = self::moduleNameForClass($class);
        return $name ? $this->find($name) : null;
    }

    /**
     * The module name in a Modules\<Name>\... class name, or null for non-module classes
     *
     * @param string $class
     * @return string|null
     */
    public static function moduleNameForClass(string $class): ?string
    {
        $parts = explode('\\', ltrim($class, '\\'));
        return (count($parts) >= 2 && $parts[0] === 'Modules') ? $parts[1] : null;
    }

    /**
     * The module list declared in config/modules.php (used before the registry exists)
     *
     * @return array<int, string>
     */
    public function declaredNames(): array
    {
        return array_values((array) config('modules.declared', config('modules.available', [])));
    }

    /**
     * Where the enabled list came from on this request: cache, registry or config
     *
     * @return string|null
     */
    public function source(): ?string
    {
        return $this->source;
    }

    /**
     * Rewrite the cache file from the registry table
     *
     * @return void
     */
    public function rebuildCache(): void
    {
        $this->clearCache();
        $names = $this->readRegistry();
        if ($names !== null) {
            $this->writeCache($names);
        }
        $this->flush();
    }

    /**
     * Delete the cache file
     *
     * @return void
     */
    public function clearCache(): void
    {
        if (is_file(self::cachePath())) {
            @unlink(self::cachePath());
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate(self::cachePath(), true);
        }
    }

    /**
     * Forget the list loaded on this request
     *
     * @return void
     */
    public function flush(): void
    {
        $this->enabled = null;
        $this->source = null;
    }

    /**
     * Read the enabled names from the registry table, or null when it
     * isn't available (no database yet, or the table doesn't exist)
     *
     * @return array<int, string>|null
     */
    protected function readRegistry(): ?array
    {
        try {
            if (!Schema::hasTable('modules')) {
                return null;
            }

            return InstalledModule::query()
                ->where('status', InstalledModule::STATUS_ENABLED)
                ->orderBy('name')
                ->pluck('name')
                ->all();
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Write the enabled list to the cache file (atomically)
     *
     * @param array<int, string> $names
     * @return void
     */
    protected function writeCache(array $names): void
    {
        $path = self::cachePath();
        $contents = '<?php return ' . var_export(['enabled' => array_values($names)], true) . ';' . PHP_EOL;
        $tmp = $path . '.' . uniqid('', true) . '.tmp';

        if (@file_put_contents($tmp, $contents) !== false) {
            @rename($tmp, $path);
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($path, true);
            }
        }
    }
}
