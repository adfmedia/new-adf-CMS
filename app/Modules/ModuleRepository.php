<?php

namespace App\Modules;

/**
 * Finds the modules enabled in this install.
 *
 * Phase 1: the enabled list still comes from config('modules.available').
 * Phase 2 swaps that source for the modules table without changing callers.
 */
class ModuleRepository
{
    /**
     * @var array<string, Module>|null
     */
    protected ?array $enabled = null;

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
        foreach ((array) config('modules.available', []) as $name) {
            $path = base_path('modules' . DIRECTORY_SEPARATOR . $name);
            if (is_dir($path)) {
                $this->enabled[$name] = Module::fromPath($name, $path);
            }
        }

        return $this->enabled;
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
     * Find the module a class belongs to, from its Modules\<Name>\... namespace
     *
     * @param string $class
     * @return Module|null
     */
    public function forClass(string $class): ?Module
    {
        $parts = explode('\\', ltrim($class, '\\'));
        if (count($parts) < 2 || $parts[0] !== 'Modules') {
            return null;
        }

        return $this->find($parts[1]);
    }

    /**
     * Forget the cached module list (used after enabling or disabling modules)
     *
     * @return void
     */
    public function flush(): void
    {
        $this->enabled = null;
    }
}
