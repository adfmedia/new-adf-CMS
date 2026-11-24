<?php

namespace App\Install;

/**
 * Where the installer keeps its markers. Plain PHP with no Laravel helpers,
 * so the /install wizard can use it before the app boots.
 */
class InstallState
{
    public function __construct(
        protected string $base_path
    ) {}

    public function basePath(string $path = ''): string
    {
        return rtrim($this->base_path, '/\\') . ($path !== '' ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }

    public function envPath(): string
    {
        return $this->basePath('.env');
    }

    public function lockPath(): string
    {
        return $this->basePath('storage/installed.lock');
    }

    public function isInstalled(): bool
    {
        return is_file($this->lockPath());
    }

    public function hasEnv(): bool
    {
        return is_file($this->envPath());
    }

    /**
     * The wizard may only run on a site with no lock file and no .env, so
     * it can never be used to take over an existing site
     */
    public function wizardAllowed(): bool
    {
        return !$this->isInstalled() && !$this->hasEnv();
    }

    public function writeLock(array $details = []): void
    {
        $details = ['installed_at' => date('c')] + $details;
        file_put_contents($this->lockPath(), json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}
