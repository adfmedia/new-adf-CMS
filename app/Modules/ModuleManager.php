<?php

namespace App\Modules;

use App\Models\InstalledModule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;
use ZipArchive;

/**
 * Installs, enables, disables, uninstalls and packages modules.
 *
 * Every action updates the registry (the `modules` table), then rebuilds
 * the enabled-modules cache and clears Laravel's caches so the change
 * shows on the next request. Artisan commands and (in Phase 3) the admin
 * module manager both call this class.
 */
class ModuleManager
{
    public function __construct(
        protected ModuleRepository $Modules
    ) {}

    /**
     * Install a module whose folder is already in modules/<Name>: run its
     * migrations, seed it on a first install, publish its assets and
     * enable it. Re-installing a module that was uninstalled with its data
     * kept skips migrations that already ran, so its data comes back.
     *
     * @param string $name
     * @param bool $seed Run the module's seeder on a first install
     * @param bool $allow_dev Allow dev-only modules (e.g. Tests) outside local/testing
     * @return InstalledModule
     */
    public function install(string $name, bool $seed = true, bool $allow_dev = false): InstalledModule
    {
        $Module = $this->requireOnDisk($name);
        $this->assertValidManifest($Module);

        if (!empty($Module->manifest['dev_only']) && !$allow_dev && !app()->environment(['local', 'testing'])) {
            throw new ModuleException($name . ' is a development-only module. Use --dev to install it anyway.');
        }

        $this->assertRequirementsMet($Module);

        $Record = InstalledModule::where('name', $name)->first();
        if ($Record && $Record->isEnabled()) {
            throw new ModuleException($name . ' is already installed and enabled.');
        }
        if ($Record && $Record->status === InstalledModule::STATUS_DISABLED) {
            throw new ModuleException($name . ' is installed but disabled. Enable it instead.');
        }
        $first_install = !$Record || $Record->installed_at === null;

        try {
            $this->runMigrations($Module);

            if ($seed && $first_install && !empty($Module->manifest['seeder'])) {
                $this->runSeeder($Module, $Module->manifest['seeder']);
            }

            $this->publishAssets($Module);
        } catch (Throwable $e) {
            $this->recordError($name, $Module, $e);
            throw new ModuleException('Installing ' . $name . ' failed: ' . $e->getMessage(), 0, $e);
        }

        $Record = $this->saveRecord($Module, InstalledModule::STATUS_ENABLED, $first_install);
        $this->afterChange('installed', $name);

        return $Record;
    }

    /**
     * Enable a module that is installed but disabled
     *
     * @param string $name
     * @return InstalledModule
     */
    public function enable(string $name): InstalledModule
    {
        $Record = $this->requireRecord($name);
        if ($Record->isEnabled()) {
            throw new ModuleException($name . ' is already enabled.');
        }
        if ($Record->status === InstalledModule::STATUS_UNINSTALLED) {
            throw new ModuleException($name . ' is uninstalled. Install it again instead.');
        }

        $Module = $this->requireOnDisk($name);
        $this->assertRequirementsMet($Module);

        try {
            // Pick up any migrations added while it was disabled
            $this->runMigrations($Module);
            $this->publishAssets($Module);
        } catch (Throwable $e) {
            $this->recordError($name, $Module, $e);
            throw new ModuleException('Enabling ' . $name . ' failed: ' . $e->getMessage(), 0, $e);
        }

        $Record = $this->saveRecord($Module, InstalledModule::STATUS_ENABLED, false);
        $this->afterChange('enabled', $name);

        return $Record;
    }

    /**
     * Disable a module. Its files and data stay; it just stops loading.
     *
     * @param string $name
     * @return InstalledModule
     */
    public function disable(string $name): InstalledModule
    {
        $Record = $this->requireRecord($name);
        $this->assertNotCore($Record, 'disabled');

        if (!$Record->isEnabled()) {
            throw new ModuleException($name . ' is not enabled.');
        }
        $this->assertNoEnabledDependents($name);

        $Record->status = InstalledModule::STATUS_DISABLED;
        $Record->status_changed_at = now();
        $Record->last_error = null;
        $Record->save();

        $this->afterChange('disabled', $name);

        return $Record;
    }

    /**
     * Uninstall a module. By default its database tables are kept, so
     * installing it again brings its data back. With $delete_data its
     * migrations are rolled back, dropping its tables.
     *
     * Files stay in modules/<Name>; removing them comes with zip handling.
     *
     * @param string $name
     * @param bool $delete_data
     * @return InstalledModule
     */
    public function uninstall(string $name, bool $delete_data = false): InstalledModule
    {
        $Record = $this->requireRecord($name);
        $this->assertNotCore($Record, 'uninstalled');

        if ($Record->status === InstalledModule::STATUS_UNINSTALLED && !$delete_data) {
            throw new ModuleException($name . ' is already uninstalled.');
        }
        $this->assertNoEnabledDependents($name);

        if ($delete_data) {
            $Module = $this->Modules->onDisk($name);
            if ($Module && is_dir($Module->migrationsPath())) {
                try {
                    Artisan::call('migrate:reset', [
                        '--path'  => $this->relativeMigrationsPath($Module),
                        '--force' => true,
                    ]);
                } catch (Throwable $e) {
                    $this->recordError($name, $Module, $e);
                    throw new ModuleException('Deleting the data for ' . $name . ' failed: ' . $e->getMessage(), 0, $e);
                }
            }
            if ($Module) {
                File::deleteDirectory($Module->publicTargetPath());
            }
        }

        $Record->status = InstalledModule::STATUS_UNINSTALLED;
        $Record->status_changed_at = now();
        $Record->last_error = null;
        if ($delete_data) {
            // A later install counts as a first install again (migrations and seeder run)
            $Record->installed_at = null;
        }
        $Record->save();

        $this->afterChange($delete_data ? 'uninstalled (data deleted)' : 'uninstalled (data kept)', $name);

        return $Record;
    }

    /**
     * Build a zip of a module ready to upload to another site:
     * <Name>-<version>.zip holding <Name>/... with a checksum added to
     * the zipped module.json.
     *
     * @param string $name
     * @param string|null $output_dir Defaults to storage/app/module-packages
     * @return string Path to the zip
     */
    public function package(string $name, ?string $output_dir = null): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new ModuleException('The PHP zip extension is needed to package modules. Enable it in php.ini.');
        }

        $Module = $this->requireOnDisk($name);
        $this->assertValidManifest($Module);

        $output_dir = $output_dir ?: storage_path('app/module-packages');
        File::ensureDirectoryExists($output_dir);
        $zip_path = $output_dir . DIRECTORY_SEPARATOR . $name . '-' . $Module->version() . '.zip';

        $files = $this->packageFiles($Module);
        $manifest = $Module->manifest;
        $manifest['checksum'] = self::checksum($Module->path, $files);

        $Zip = new ZipArchive();
        if ($Zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ModuleException('Could not create ' . $zip_path);
        }
        $Zip->addFromString($name . '/module.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        foreach ($files as $relative) {
            $Zip->addFile($Module->path . DIRECTORY_SEPARATOR . $relative, $name . '/' . str_replace('\\', '/', $relative));
        }
        $Zip->close();

        return $zip_path;
    }

    /**
     * SHA-256 over a module's files (sorted paths and contents), excluding module.json
     *
     * @param string $module_path
     * @param array<int, string> $relative_files
     * @return string
     */
    public static function checksum(string $module_path, array $relative_files): string
    {
        sort($relative_files);
        $context = hash_init('sha256');
        foreach ($relative_files as $relative) {
            hash_update($context, str_replace('\\', '/', $relative) . "\0");
            hash_update_file($context, $module_path . DIRECTORY_SEPARATOR . $relative);
        }

        return hash_final($context);
    }

    /**
     * Rebuild the enabled-modules cache and clear Laravel's caches
     *
     * @return void
     */
    public function refresh(): void
    {
        $this->Modules->rebuildCache();
        try {
            Artisan::call('optimize:clear');
        } catch (Throwable $e) {
            Log::warning('Module change: clearing caches failed', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Rows for a status listing: every module on disk and in the registry
     *
     * @return array<int, array<string, mixed>>
     */
    public function statusList(): array
    {
        $records = InstalledModule::query()->get()->keyBy('name');
        $rows = [];

        foreach ($this->Modules->allOnDisk() as $name => $Module) {
            $Record = $records->get($name);
            $rows[$name] = [
                'name'      => $name,
                'version'   => $Module->version(),
                'core'      => $Module->isCore(),
                'status'    => $Record?->status ?? 'not installed',
                'installed' => $Record?->version,
                'error'     => $Record?->last_error,
            ];
        }
        foreach ($records as $name => $Record) {
            if (!isset($rows[$name])) {
                $rows[$name] = [
                    'name'      => $name,
                    'version'   => null,
                    'core'      => $Record->is_core,
                    'status'    => $Record->status . ' (files missing)',
                    'installed' => $Record->version,
                    'error'     => $Record->last_error,
                ];
            }
        }
        ksort($rows);

        return array_values($rows);
    }

    /* ------------------------------------------------------------------ */

    protected function requireOnDisk(string $name): Module
    {
        $Module = $this->Modules->onDisk($name);
        if (!$Module) {
            throw new ModuleException('There is no module folder modules/' . $name . '.');
        }

        return $Module;
    }

    protected function requireRecord(string $name): InstalledModule
    {
        $Record = InstalledModule::where('name', $name)->first();
        if (!$Record) {
            throw new ModuleException($name . ' is not installed.');
        }

        return $Record;
    }

    protected function assertValidManifest(Module $Module): void
    {
        $manifest = $Module->manifest;
        if (empty($manifest)) {
            throw new ModuleException($Module->name . ' has no module.json.');
        }
        if (($manifest['name'] ?? null) !== $Module->name) {
            throw new ModuleException('The name in ' . $Module->name . '/module.json does not match its folder.');
        }
        if (!preg_match('/^\d+\.\d+\.\d+$/', (string) ($manifest['version'] ?? ''))) {
            throw new ModuleException($Module->name . '/module.json needs a version like 1.0.0.');
        }

        $php = $manifest['requires']['php'] ?? null;
        if ($php && !self::satisfies(PHP_VERSION, $php)) {
            throw new ModuleException($Module->name . ' needs PHP ' . $php . ' (this server has ' . PHP_VERSION . ').');
        }
    }

    protected function assertRequirementsMet(Module $Module): void
    {
        $missing = array_filter(
            (array) ($Module->manifest['requires']['modules'] ?? []),
            fn ($required) => !$this->isEnabledInRegistry($required)
        );

        if ($missing) {
            throw new ModuleException($Module->name . ' needs these modules enabled first: ' . implode(', ', $missing) . '.');
        }
    }

    protected function assertNoEnabledDependents(string $name): void
    {
        $dependents = [];
        $enabled = InstalledModule::where('status', InstalledModule::STATUS_ENABLED)->where('name', '!=', $name)->get();

        foreach ($enabled as $Other) {
            $Module = $this->Modules->onDisk($Other->name);
            $requires = (array) ($Module?->manifest['requires']['modules'] ?? []);
            if (in_array($name, $requires, true)) {
                $dependents[] = $Other->name;
            }
        }

        if ($dependents) {
            throw new ModuleException($name . ' is needed by ' . implode(', ', $dependents) . '. Disable those first.');
        }
    }

    protected function assertNotCore(InstalledModule $Record, string $action): void
    {
        if ($Record->is_core) {
            throw new ModuleException($Record->name . ' is a core module and can\'t be ' . $action . '.');
        }
    }

    protected function isEnabledInRegistry(string $name): bool
    {
        return InstalledModule::where('name', $name)->where('status', InstalledModule::STATUS_ENABLED)->exists();
    }

    protected function runMigrations(Module $Module): void
    {
        if (!is_dir($Module->migrationsPath())) {
            return;
        }

        $exit = Artisan::call('migrate', [
            '--path'  => $this->relativeMigrationsPath($Module),
            '--force' => true,
        ]);
        if ($exit !== 0) {
            throw new ModuleException(trim(Artisan::output()) ?: 'Migrations failed.');
        }
    }

    protected function runSeeder(Module $Module, string $seeder): void
    {
        if (!class_exists($seeder)) {
            throw new ModuleException('Seeder ' . $seeder . ' for ' . $Module->name . ' was not found.');
        }

        Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
    }

    protected function publishAssets(Module $Module): void
    {
        if (!is_dir($Module->publicSourcePath())) {
            return;
        }

        File::deleteDirectory($Module->publicTargetPath());
        File::ensureDirectoryExists($Module->publicTargetPath());
        File::copyDirectory($Module->publicSourcePath(), $Module->publicTargetPath());
    }

    protected function relativeMigrationsPath(Module $Module): string
    {
        return 'modules/' . $Module->name . '/Database/migrations';
    }

    protected function saveRecord(Module $Module, string $status, bool $first_install): InstalledModule
    {
        $Record = InstalledModule::firstOrNew(['name' => $Module->name]);
        $Record->fill([
            'version'           => $Module->version(),
            'status'            => $status,
            'is_core'           => $Module->isCore(),
            'manifest'          => $Module->manifest,
            'checksum'          => $Module->manifest['checksum'] ?? null,
            'status_changed_at' => now(),
            'last_error'        => null,
        ]);
        if ($first_install || $Record->installed_at === null) {
            $Record->installed_at = now();
        }
        $Record->save();

        return $Record;
    }

    protected function recordError(string $name, Module $Module, Throwable $e): void
    {
        Log::error('Module ' . $name . ' action failed', ['exception' => $e]);

        try {
            $Record = InstalledModule::firstOrNew(['name' => $name]);
            if (!$Record->exists) {
                $Record->fill([
                    'version'  => $Module->version(),
                    'status'   => InstalledModule::STATUS_UNINSTALLED,
                    'is_core'  => $Module->isCore(),
                    'manifest' => $Module->manifest,
                ]);
            }
            $Record->last_error = $e->getMessage();
            $Record->save();
        } catch (Throwable $ignored) {
        }
    }

    protected function afterChange(string $action, string $name): void
    {
        Log::info('Module ' . $name . ' ' . $action);
        $this->refresh();
    }

    /**
     * Files to put in a module package (relative to the module folder):
     * everything except module.json and dotfiles
     *
     * @param Module $Module
     * @return array<int, string>
     */
    protected function packageFiles(Module $Module): array
    {
        $files = [];
        foreach (File::allFiles($Module->path, false) as $File) {
            $relative = $File->getRelativePathname();
            if ($relative === 'module.json' || str_starts_with(basename($relative), '.')) {
                continue;
            }
            $files[] = $relative;
        }
        sort($files);

        return $files;
    }

    /**
     * Whether a version satisfies a simple constraint such as ">=8.3" or "^1.2"
     *
     * @param string $version
     * @param string $constraint
     * @return bool
     */
    public static function satisfies(string $version, string $constraint): bool
    {
        $constraint = trim($constraint);
        if (preg_match('/^\^(\d+)(\.\d+)*/', $constraint, $m)) {
            $min = ltrim($constraint, '^');
            return version_compare($version, $min, '>=') && (int) explode('.', $version)[0] === (int) $m[1];
        }
        if (preg_match('/^(>=|<=|>|<|==|=)?\s*([\d.]+)$/', $constraint, $m)) {
            return version_compare($version, $m[2], $m[1] ?: '>=');
        }

        return true;
    }
}
