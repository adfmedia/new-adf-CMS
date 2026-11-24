<?php

namespace App\Modules;

use App\Models\InstalledModule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

/**
 * Installs or upgrades a module from an uploaded zip (as built by
 * cms:module:package).
 *
 * Nothing is extracted until every entry in the zip has been checked.
 * The module is unpacked into a staging folder, its checksum verified,
 * and only then moved into modules/<Name>. An existing copy is moved to
 * modules/.backup first, and put back if anything fails.
 */
class ModuleZipInstaller
{
    /** Largest zip accepted, in bytes */
    public const MAX_ZIP_BYTES = 50 * 1024 * 1024;

    /** Largest total size once unpacked, in bytes (guards against zip bombs) */
    public const MAX_UNPACKED_BYTES = 200 * 1024 * 1024;

    /** Most files a module zip may hold */
    public const MAX_ENTRIES = 5000;

    /** File types a module may contain */
    public const ALLOWED_EXTENSIONS = [
        'php', 'json', 'md', 'txt', 'csv', 'xml', 'yml', 'yaml',
        'css', 'js', 'mjs', 'map',
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'avif', 'ico',
        'woff', 'woff2', 'ttf', 'otf', 'eot',
    ];

    /**
     * File types allowed in Public/ (copied into the web root, so never PHP,
     * HTML or server config)
     */
    public const PUBLIC_EXTENSIONS = [
        'css', 'js', 'mjs', 'map', 'json', 'txt',
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'avif', 'ico',
        'woff', 'woff2', 'ttf', 'otf', 'eot',
    ];

    public function __construct(
        protected ModuleManager $Manager,
        protected ModuleRepository $Modules
    ) {}

    /**
     * Install or upgrade a module from a zip file
     *
     * @param string $zip_path Path to the uploaded zip
     * @param bool $allow_reinstall Allow installing the same version again
     * @return array{record: InstalledModule, action: string, from: ?string}
     */
    public function installFromZip(string $zip_path, bool $allow_reinstall = false): array
    {
        if (!class_exists(ZipArchive::class)) {
            throw new ModuleException('The PHP zip extension is needed to install module zips. Enable it in php.ini.');
        }
        if (!is_file($zip_path) || filesize($zip_path) > self::MAX_ZIP_BYTES) {
            throw new ModuleException('The zip is missing or larger than ' . (self::MAX_ZIP_BYTES / 1024 / 1024) . 'MB.');
        }

        $Zip = new ZipArchive();
        if ($Zip->open($zip_path, ZipArchive::RDONLY) !== true) {
            throw new ModuleException('That file is not a valid zip.');
        }

        try {
            [$name, $manifest] = $this->inspect($Zip);
            $Existing = $this->Modules->onDisk($name);
            $Record = InstalledModule::where('name', $name)->first();
            $this->checkVersion($name, $manifest, $Existing, $allow_reinstall);

            $staging = storage_path('app/module-staging/' . Str::uuid());
            File::ensureDirectoryExists($staging);
            if (!$Zip->extractTo($staging)) {
                throw new ModuleException('The zip could not be unpacked.');
            }
        } finally {
            $Zip->close();
        }

        $staged = $staging . DIRECTORY_SEPARATOR . $name;
        $backup = null;
        $target = base_path('modules' . DIRECTORY_SEPARATOR . $name);
        $migrations_before = null;

        try {
            $this->verifyChecksum($staged, $manifest);

            // Move any existing copy aside, then put the new one in place
            if (is_dir($target)) {
                $backup = $this->backupFolder($name, $Existing?->version() ?? 'unknown');
                if (!@rename($target, $backup)) {
                    throw new ModuleException('Could not move the existing ' . $name . ' folder aside.');
                }
            }
            if (!@rename($staged, $target)) {
                throw new ModuleException('Could not move ' . $name . ' into modules/.');
            }

            $this->Modules->flush();
            $migrations_before = $this->migrationCount();

            if ($Record && in_array($Record->status, [InstalledModule::STATUS_ENABLED, InstalledModule::STATUS_DISABLED], true)) {
                $action = 'upgraded';
                $Record = $this->Manager->upgrade($name);
            } else {
                $action = 'installed';
                $Record = $this->Manager->install($name);
            }
        } catch (Throwable $e) {
            $this->rollBack($name, $target, $backup, $migrations_before);
            File::deleteDirectory($staging);
            throw $e instanceof ModuleException ? $e : new ModuleException($e->getMessage(), 0, $e);
        }

        File::deleteDirectory($staging);

        return [
            'record' => $Record,
            'action' => $action,
            'from'   => $Existing?->version(),
        ];
    }

    /**
     * Check every entry in the zip before anything is extracted, and read
     * its module.json
     *
     * @param ZipArchive $Zip
     * @return array{0: string, 1: array}
     */
    public function inspect(ZipArchive $Zip): array
    {
        if ($Zip->numFiles === 0) {
            throw new ModuleException('The zip is empty.');
        }
        if ($Zip->numFiles > self::MAX_ENTRIES) {
            throw new ModuleException('The zip holds more than ' . self::MAX_ENTRIES . ' files.');
        }

        $top = null;
        $unpacked = 0;

        for ($i = 0; $i < $Zip->numFiles; $i++) {
            $stat = $Zip->statIndex($i);
            $entry = (string) $stat['name'];

            $this->assertSafePath($entry);
            $this->assertNotSymlink($Zip, $i, $entry);

            $parts = explode('/', $entry);
            if ($top === null) {
                $top = $parts[0];
            } elseif ($parts[0] !== $top) {
                throw new ModuleException('The zip must hold a single module folder. Found "' . $top . '" and "' . $parts[0] . '".');
            }

            $unpacked += (int) $stat['size'];
            if ($unpacked > self::MAX_UNPACKED_BYTES) {
                throw new ModuleException('The zip unpacks to more than ' . (self::MAX_UNPACKED_BYTES / 1024 / 1024) . 'MB.');
            }

            if (str_ends_with($entry, '/')) {
                continue; // a folder
            }
            $this->assertAllowedFile($entry, $parts);
        }

        if (!preg_match('/^[A-Z][A-Za-z0-9]*$/', (string) $top)) {
            throw new ModuleException('The module folder name "' . $top . '" must be letters and numbers, starting with a capital, e.g. News.');
        }

        $json = $Zip->getFromName($top . '/module.json');
        if ($json === false) {
            throw new ModuleException('The zip has no ' . $top . '/module.json.');
        }
        $manifest = json_decode($json, true);
        if (!is_array($manifest)) {
            throw new ModuleException($top . '/module.json is not valid JSON.');
        }
        if (($manifest['name'] ?? null) !== $top) {
            throw new ModuleException('The name in module.json ("' . ($manifest['name'] ?? '') . '") does not match the folder "' . $top . '".');
        }
        if (!preg_match('/^\d+\.\d+\.\d+$/', (string) ($manifest['version'] ?? ''))) {
            throw new ModuleException('module.json needs a version like 1.0.0.');
        }

        return [$top, $manifest];
    }

    protected function assertSafePath(string $entry): void
    {
        if ($entry === ''
            || str_contains($entry, "\0")
            || str_contains($entry, '\\')
            || str_starts_with($entry, '/')
            || preg_match('/^[A-Za-z]:/', $entry)
            || in_array('..', explode('/', $entry), true)
            || in_array('.', explode('/', rtrim($entry, '/')), true)
        ) {
            throw new ModuleException('The zip contains an unsafe path: ' . Str::limit($entry, 80));
        }
    }

    protected function assertNotSymlink(ZipArchive $Zip, int $index, string $entry): void
    {
        $opsys = null;
        $attr = null;
        if ($Zip->getExternalAttributesIndex($index, $opsys, $attr) && $opsys === ZipArchive::OPSYS_UNIX) {
            $mode = ($attr >> 16) & 0xF000;
            if ($mode === 0xA000) {
                throw new ModuleException('The zip contains a symbolic link, which is not allowed: ' . Str::limit($entry, 80));
            }
        }
    }

    /**
     * @param string $entry
     * @param array<int, string> $parts Path segments, starting with the module folder
     */
    protected function assertAllowedFile(string $entry, array $parts): void
    {
        $file = end($parts);
        if (str_starts_with($file, '.')) {
            throw new ModuleException('Hidden files are not allowed in a module: ' . Str::limit($entry, 80));
        }

        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new ModuleException('Files of type ".' . $extension . '" are not allowed in a module: ' . Str::limit($entry, 80));
        }

        // Public/ is copied into the web root: static files only
        if (($parts[1] ?? null) === 'Public' && !in_array($extension, self::PUBLIC_EXTENSIONS, true)) {
            throw new ModuleException('Only images, fonts, CSS and JavaScript may go in Public/: ' . Str::limit($entry, 80));
        }
    }

    protected function checkVersion(string $name, array $manifest, ?Module $Existing, bool $allow_reinstall): void
    {
        $Record = InstalledModule::where('name', $name)->first();

        // A core module can only be replaced by a newer version of itself
        if (($Record?->is_core || $Existing?->isCore()) && empty($manifest['core'])) {
            throw new ModuleException($name . ' is a core module, and this zip doesn\'t mark it as core.');
        }
        if (!$Existing) {
            return;
        }

        $compare = version_compare($manifest['version'], $Existing->version());
        if ($compare < 0) {
            throw new ModuleException('This zip is ' . $name . ' ' . $manifest['version'] . ', older than the ' . $Existing->version() . ' already here.');
        }
        if ($compare === 0 && !$allow_reinstall && $Record && $Record->status !== InstalledModule::STATUS_UNINSTALLED) {
            throw new ModuleException($name . ' ' . $manifest['version'] . ' is already installed.');
        }
    }

    protected function verifyChecksum(string $module_path, array $manifest): void
    {
        if (empty($manifest['checksum'])) {
            return;
        }

        $files = [];
        foreach (File::allFiles($module_path, true) as $File) {
            $relative = str_replace('\\', '/', $File->getRelativePathname());
            if ($relative !== 'module.json') {
                $files[] = $relative;
            }
        }

        if (!hash_equals((string) $manifest['checksum'], ModuleManager::checksum($module_path, $files))) {
            throw new ModuleException('The module\'s checksum doesn\'t match its files, so the zip may be damaged or changed. Package it again.');
        }
    }

    protected function backupFolder(string $name, string $version): string
    {
        $dir = base_path('modules' . DIRECTORY_SEPARATOR . '.backup');
        File::ensureDirectoryExists($dir);

        return $dir . DIRECTORY_SEPARATOR . $name . '-' . $version . '-' . date('Ymd-His');
    }

    /**
     * Undo a failed install: roll back any migrations it ran and put the
     * previous files back
     */
    protected function rollBack(string $name, string $target, ?string $backup, ?int $migrations_before): void
    {
        try {
            if ($migrations_before !== null && $this->migrationCount() > $migrations_before && is_dir($target . '/Database/migrations')) {
                Artisan::call('migrate:rollback', [
                    '--path'  => 'modules/' . $name . '/Database/migrations',
                    '--force' => true,
                ]);
            }
        } catch (Throwable $e) {
            Log::error('Rolling back migrations for ' . $name . ' failed', ['exception' => $e]);
        }

        if ($backup && is_dir($backup)) {
            File::deleteDirectory($target);
            @rename($backup, $target);
        } elseif (!$backup) {
            File::deleteDirectory($target);
        }

        $this->Modules->flush();
        $this->Manager->refresh();
    }

    protected function migrationCount(): int
    {
        try {
            return DB::table(config('database.migrations.table', 'migrations'))->count();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
