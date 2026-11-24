<?php

namespace App\Modules;

use Illuminate\Support\Str;
use RuntimeException;

/**
 * A single CMS module, read from modules/<Name>/module.json
 */
class Module
{
    /**
     * @param string $name      The module folder name, e.g. "News"
     * @param string $path      Absolute path to the module folder
     * @param array  $manifest  The decoded module.json contents
     */
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly array $manifest = []
    ) {}

    /**
     * Load a module from its folder, reading its module.json manifest
     *
     * @param string $name
     * @param string $path
     * @return self
     */
    public static function fromPath(string $name, string $path): self
    {
        $manifest = [];
        $manifest_path = $path . DIRECTORY_SEPARATOR . 'module.json';

        if (is_file($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);
            if (!is_array($manifest)) {
                throw new RuntimeException('The module.json for module ' . $name . ' is not valid JSON.');
            }
        }

        return new self($name, $path, $manifest);
    }

    /**
     * The module's version from its manifest
     *
     * @return string
     */
    public function version(): string
    {
        return (string) ($this->manifest['version'] ?? '0.0.0');
    }

    /**
     * Whether this is a core module that is always installed
     *
     * @return bool
     */
    public function isCore(): bool
    {
        return (bool) ($this->manifest['core'] ?? false);
    }

    /**
     * The view namespace for the module, e.g. "news" so views are
     * referenced as news::listing. Defaults to the kebab-cased
     * module name.
     *
     * @return string
     */
    public function viewNamespace(): string
    {
        return $this->manifest['provides']['views'] ?? Str::kebab($this->name);
    }

    /**
     * Path to a sub-directory within the module
     *
     * @param string $sub_path
     * @return string
     */
    public function subPath(string $sub_path = ''): string
    {
        return $this->path . ($sub_path !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $sub_path) : '');
    }

    /**
     * Path to the module's views
     *
     * @return string
     */
    public function viewsPath(): string
    {
        return $this->subPath('Views');
    }

    /**
     * Path to the module's migrations
     *
     * @return string
     */
    public function migrationsPath(): string
    {
        return $this->subPath('Database/migrations');
    }

    /**
     * Path to the module's public assets (copied to public/modules/<namespace>)
     *
     * @return string
     */
    public function publicSourcePath(): string
    {
        return $this->subPath('Public');
    }

    /**
     * Path where the module's public assets are published
     *
     * @return string
     */
    public function publicTargetPath(): string
    {
        return public_path('modules' . DIRECTORY_SEPARATOR . $this->viewNamespace());
    }

    /**
     * Public URL to one of the module's published assets
     *
     * @param string $file
     * @return string
     */
    public function assetUrl(string $file): string
    {
        return asset('modules/' . $this->viewNamespace() . '/' . ltrim($file, '/'));
    }
}
