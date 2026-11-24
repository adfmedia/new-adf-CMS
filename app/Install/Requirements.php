<?php

namespace App\Install;

/**
 * Server checks run before installing. Plain PHP so the /install wizard can
 * use it before Laravel boots.
 */
class Requirements
{
    public const MIN_PHP = '8.3.0';

    /** Extensions the CMS can't run without */
    public const EXTENSIONS = [
        'pdo_mysql' => 'Database connections',
        'mbstring'  => 'Text handling',
        'openssl'   => 'Encryption',
        'fileinfo'  => 'File uploads',
        'tokenizer' => 'Laravel',
        'ctype'     => 'Laravel',
        'json'      => 'Laravel',
        'dom'       => 'Laravel',
        'xml'       => 'Laravel',
        'curl'      => 'HTTP requests',
        'zip'       => 'Module zips',
    ];

    /** Folders the installer and the site must be able to write to */
    public const WRITABLE = [
        ''                  => 'Site root (to write .env)',
        'storage'           => 'storage',
        'bootstrap/cache'   => 'bootstrap/cache',
        'modules'           => 'modules',
        'public'            => 'public (for public/modules)',
    ];

    public function __construct(
        protected string $base_path
    ) {}

    /**
     * Every check, each as [label, ok, detail, required]
     *
     * @return array<int, array{label: string, ok: bool, detail: string, required: bool}>
     */
    public function checks(): array
    {
        $checks = [];

        $checks[] = [
            'label'    => 'PHP ' . self::MIN_PHP . ' or newer',
            'ok'       => version_compare(PHP_VERSION, self::MIN_PHP, '>='),
            'detail'   => 'This server has PHP ' . PHP_VERSION,
            'required' => true,
        ];

        foreach (self::EXTENSIONS as $extension => $why) {
            $checks[] = [
                'label'    => 'PHP extension: ' . $extension,
                'ok'       => extension_loaded($extension),
                'detail'   => $why,
                'required' => true,
            ];
        }

        $checks[] = [
            'label'    => 'PHP extension: gd or imagick',
            'ok'       => extension_loaded('gd') || extension_loaded('imagick'),
            'detail'   => 'Image resizing',
            'required' => true,
        ];

        foreach (self::WRITABLE as $path => $label) {
            $full = $this->base_path . ($path !== '' ? DIRECTORY_SEPARATOR . $path : '');
            $checks[] = [
                'label'    => 'Writable: ' . $label,
                'ok'       => is_dir($full) && is_writable($full),
                'detail'   => $full,
                'required' => true,
            ];
        }

        $checks[] = [
            'label'    => 'Code libraries (vendor/) included',
            'ok'       => is_file($this->base_path . '/vendor/autoload.php'),
            'detail'   => 'Run composer install, or use a release package',
            'required' => true,
        ];

        $checks[] = [
            'label'    => 'Built CSS and JavaScript (public/build) included',
            'ok'       => is_file($this->base_path . '/public/build/manifest.json'),
            'detail'   => 'Run npm run prod, or use a release package',
            'required' => true,
        ];

        return $checks;
    }

    public function passes(): bool
    {
        foreach ($this->checks() as $check) {
            if ($check['required'] && !$check['ok']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Labels of the failed required checks
     *
     * @return array<int, string>
     */
    public function failures(): array
    {
        return array_values(array_map(
            fn ($check) => $check['label'] . ' (' . $check['detail'] . ')',
            array_filter($this->checks(), fn ($check) => $check['required'] && !$check['ok'])
        ));
    }
}
