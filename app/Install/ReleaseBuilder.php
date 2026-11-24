<?php

namespace App\Install;

use App\Modules\ModuleManager;
use App\Modules\ModuleRepository;
use Closure;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Builds the release package: one zip that installs on a server with no
 * SSH, Composer or Node, plus a zip of each optional module for uploading
 * in Settings > Modules later.
 *
 * The package is made from the last commit (git archive), so uncommitted
 * changes and local files like .env never go into it.
 */
class ReleaseBuilder
{
    /** Paths removed from the package: development-only files */
    public const DEV_PATHS = [
        'tests',
        'phpunit.xml',
        '.editorconfig',
        '.idea',
        '.github',
        'README.md',
        'modules/Tests',
        'modules/.backup',
        'composer.phar',
        'composer-setup.php',
    ];

    /** @var Closure(string): void */
    protected Closure $say;

    public function __construct(
        protected ModuleRepository $Modules,
        protected ModuleManager $Manager
    ) {
        $this->say = function (string $line) {};
    }

    public function onProgress(Closure $say): self
    {
        $this->say = $say;
        return $this;
    }

    /**
     * @param string|null $output_dir Defaults to storage/app/releases
     * @param string $ref The git commit or branch to build from
     * @return array{zip: string, sha256: string, version: string, modules: array<int, string>, commit: string}
     */
    public function build(?string $output_dir = null, string $ref = 'HEAD'): array
    {
        $this->assertTools();

        $version = (string) config('cms.version');
        $name = 'adfmedia-cms-' . $version;
        $output_dir = $output_dir ?: storage_path('app/releases');
        $work = storage_path('app/release-build/' . bin2hex(random_bytes(4)));
        $staging = $work . DIRECTORY_SEPARATOR . $name;
        File::ensureDirectoryExists($output_dir);
        File::ensureDirectoryExists($staging);

        try {
            $commit = trim($this->run(['git', 'rev-parse', '--short', $ref], base_path()));
            ($this->say)('Building ' . $name . ' from commit ' . $commit);

            // 1. The committed code
            $archive = $work . DIRECTORY_SEPARATOR . 'source.zip';
            $this->run(['git', 'archive', '--format=zip', '-o', $archive, $ref], base_path());
            $this->extract($archive, $staging);
            @unlink($archive);
            ($this->say)('Exported the code');

            // 2. Production code libraries, starting from the local vendor/ so it works offline
            if (is_dir(base_path('vendor'))) {
                File::copyDirectory(base_path('vendor'), $staging . '/vendor');
            }
            $this->run(array_merge($this->composer(), [
                'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress', '--prefer-dist',
            ]), $staging, 900);
            ($this->say)('Installed production libraries (no dev packages)');

            // 3. Remove development files and local state
            foreach (self::DEV_PATHS as $path) {
                $full = $staging . DIRECTORY_SEPARATOR . $path;
                is_dir($full) ? File::deleteDirectory($full) : @unlink($full);
            }
            @unlink($staging . '/bootstrap/cache/modules.php');
            @unlink($staging . '/.env');
            @unlink($staging . '/storage/installed.lock');
            $this->sanitiseEnvExample($staging . '/.env.example');

            // 4. Checks
            foreach (['vendor/autoload.php', 'public/build/manifest.json', 'public/install/index.php', 'artisan', '.env.example'] as $required) {
                if (!is_file($staging . '/' . $required)) {
                    throw new RuntimeException('The package is missing ' . $required . '.');
                }
            }

            // 5. Guide and version
            file_put_contents($staging . '/VERSION', $version . PHP_EOL);
            file_put_contents($staging . '/INSTALL.md', $this->guide($version));

            // 6. The release zip
            $zip = $output_dir . DIRECTORY_SEPARATOR . $name . '.zip';
            $this->zipFolder($staging, $name, $zip);
            $sha256 = hash_file('sha256', $zip);
            file_put_contents($zip . '.sha256', $sha256 . '  ' . basename($zip) . PHP_EOL);
            ($this->say)('Wrote ' . $zip . ' (' . number_format(filesize($zip) / 1024 / 1024, 1) . 'MB)');

            // 7. Optional module zips
            $module_dir = $output_dir . DIRECTORY_SEPARATOR . $name . '-modules';
            File::deleteDirectory($module_dir);
            File::ensureDirectoryExists($module_dir);
            $modules = [];
            foreach ($this->Modules->allOnDisk() as $module_name => $Module) {
                if ($Module->isCore() || !empty($Module->manifest['dev_only'])) {
                    continue;
                }
                $this->Manager->package($module_name, $module_dir);
                $modules[] = $module_name;
            }
            ($this->say)('Packaged ' . count($modules) . ' optional modules into ' . $module_dir);
        } finally {
            File::deleteDirectory($work);
        }

        return [
            'zip'     => $zip,
            'sha256'  => $sha256,
            'version' => $version,
            'modules' => $modules,
            'commit'  => $commit,
        ];
    }

    /**
     * Tracked files with uncommitted changes, which won't be in the package
     *
     * @return array<int, string>
     */
    public function uncommittedChanges(): array
    {
        $status = $this->run(['git', 'status', '--porcelain', '--untracked-files=no'], base_path());

        return array_values(array_filter(array_map('trim', explode("\n", $status))));
    }

    protected function assertTools(): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is needed to build a release.');
        }
        $this->run(['git', '--version'], base_path());
        $this->composer();
    }

    /**
     * The command to run Composer: composer.phar in the site root, or composer on the PATH
     *
     * @return array<int, string>
     */
    protected function composer(): array
    {
        if (is_file(base_path('composer.phar'))) {
            return [PHP_BINARY, base_path('composer.phar')];
        }

        $finder = new \Symfony\Component\Process\ExecutableFinder();
        $composer = $finder->find('composer');
        if (!$composer) {
            throw new RuntimeException('Composer was not found. Put composer.phar in the site root or composer on the PATH.');
        }

        return [$composer];
    }

    protected function run(array $command, string $cwd, int $timeout = 120): string
    {
        $Process = new Process($command, $cwd, ['COMPOSER_ALLOW_SUPERUSER' => '1'], null, $timeout);
        $Process->run();

        if (!$Process->isSuccessful()) {
            throw new RuntimeException(implode(' ', array_map('basename', array_slice($command, 0, 2))) . ' failed: ' . trim($Process->getErrorOutput() ?: $Process->getOutput()));
        }

        return $Process->getOutput();
    }

    protected function extract(string $zip_path, string $to): void
    {
        $Zip = new ZipArchive();
        if ($Zip->open($zip_path) !== true || !$Zip->extractTo($to)) {
            throw new RuntimeException('Could not unpack the exported code.');
        }
        $Zip->close();
    }

    /**
     * Zip a folder, keeping hidden files (.htaccess, .env.example, .gitignore)
     * and empty folders, under a top-level folder name
     */
    protected function zipFolder(string $folder, string $top, string $zip_path): void
    {
        @unlink($zip_path);
        $Zip = new ZipArchive();
        if ($Zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create ' . $zip_path);
        }

        $Iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($Iterator as $Item) {
            $relative = str_replace('\\', '/', substr($Item->getPathname(), strlen($folder) + 1));
            $Item->isDir()
                ? $Zip->addEmptyDir($top . '/' . $relative)
                : $Zip->addFile($Item->getPathname(), $top . '/' . $relative);
        }

        $Zip->close();
    }

    /**
     * Blank the third-party credentials in .env.example so they aren't
     * handed to every client
     */
    protected function sanitiseEnvExample(string $path): void
    {
        $contents = (string) file_get_contents($path);
        foreach (EnvWriter::BLANKED_KEYS as $key) {
            $contents = preg_replace('/^' . preg_quote($key, '/') . '=.*$/m', $key . '=', $contents);
        }
        file_put_contents($path, $contents);
    }

    protected function guide(string $version): string
    {
        return <<<MD
# adfmedia CMS {$version}: installing a new site

You need PHP 8.3 or newer with these extensions: pdo_mysql, mbstring, openssl,
fileinfo, tokenizer, ctype, json, dom, xml, curl, zip, and gd or imagick.
You also need a MySQL or MariaDB database server. SSH, Composer and Node are not needed.

## 1. Upload

1. Unzip this package and upload everything inside the `adfmedia-cms-{$version}`
   folder to the site's folder on the server (for example `httpdocs` in Plesk).
2. Point the domain's **document root** at the `public` folder
   (in Plesk: Hosting Settings > Document root > `httpdocs/public`).
3. Make sure PHP can write to `storage`, `bootstrap/cache`, `modules`, `public`
   and the site folder itself (to create `.env`).

## 2. Install

1. Open the site in a browser. With no `.env` yet, it goes straight to `/install/`.
2. Follow the steps:
   - **Server:** fix anything marked Missing, then continue.
   - **Site:** name, URL, environment (choose Live for a real site), timezone.
   - **Database:** either let the installer create the database and its user
     (give it a MySQL login that can create them, such as the Plesk database admin),
     or create an empty database in Plesk first and choose "Use a database I've
     already made". Keep a copy of the database password it shows.
   - **Admin:** the first CMS user. Use a password of at least 12 characters.
   - **Modules:** tick the features this client needs. Core modules are always installed.
3. Click Install. When it says Installed, **delete the `public/install` folder**.
4. Sign in at `/adm`.

## From the command line instead

If you do have SSH:

```
php artisan cms:install
```

It asks the same questions. Run `php artisan help cms:install` for the options to script it.

## After installing

- **More modules:** in the CMS, go to Settings > Modules and upload a module zip
  (the `adfmedia-cms-{$version}-modules` folder has one for each optional module).
- **Another admin, or a forgotten password:** `php artisan cms:admin:create name@example.com`
- **HTTPS and www:** `public/.htaccess` sends visitors to `https://www.` on live
  domains (not on `.laragon`, `.test`, `.local` or `localhost`). If a site
  shouldn't use www, remove the "Force www" lines.
- **Support details card:** super admins can hide it in Settings > CMS settings.

MD;
    }
}
