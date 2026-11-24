<?php

namespace App\Install;

use RuntimeException;

/**
 * Writes the site's .env from .env.example with the installer's answers.
 * Plain PHP so the /install wizard can use it before Laravel boots.
 */
class EnvWriter
{
    /**
     * Keys in .env.example that hold another site's credentials and must
     * never be copied into a new site's .env
     */
    public const BLANKED_KEYS = [
        'RECAPTCHA_SITE_KEY', 'RECAPTCHA_SECRET_KEY',
        'CLOUDFLARE_EMAIL', 'CLOUDFLARE_API_KEY', 'CLOUDFLARE_DOMAIN',
        'TINIFY_API_KEY',
        'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY',
        'REDIS_PASSWORD', 'MAIL_PASSWORD',
    ];

    public function __construct(
        protected string $base_path
    ) {}

    public static function generateAppKey(): string
    {
        return 'base64:' . base64_encode(random_bytes(32));
    }

    /**
     * Build the .env contents: .env.example with $values set
     *
     * @param array<string, string|int|bool|null> $values
     * @return string
     */
    public function build(array $values): string
    {
        $example = $this->base_path . DIRECTORY_SEPARATOR . '.env.example';
        $contents = is_file($example) ? file_get_contents($example) : '';

        foreach (self::BLANKED_KEYS as $key) {
            if (!array_key_exists($key, $values)) {
                $values[$key] = '';
            }
        }

        foreach ($values as $key => $value) {
            if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
                throw new RuntimeException('Invalid .env key ' . $key);
            }

            $line = $key . '=' . self::formatValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            $contents = preg_match($pattern, $contents)
                ? preg_replace_callback($pattern, fn () => $line, $contents, 1)
                : rtrim($contents) . PHP_EOL . $line . PHP_EOL;
        }

        return $contents;
    }

    /**
     * Write .env, refusing to overwrite an existing one
     *
     * @param array<string, string|int|bool|null> $values
     * @return string Path written
     */
    public function write(array $values, bool $overwrite = false): string
    {
        $path = $this->base_path . DIRECTORY_SEPARATOR . '.env';
        if (is_file($path) && !$overwrite) {
            throw new RuntimeException('A .env file already exists. The installer will not overwrite it.');
        }

        $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (file_put_contents($tmp, $this->build($values)) === false) {
            throw new RuntimeException('Could not write ' . $path . '. Check the site folder is writable.');
        }
        @chmod($tmp, 0640);
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException('Could not write ' . $path . '.');
        }

        return $path;
    }

    /**
     * Format a value for .env: booleans as true/false, and quoted when it
     * has spaces or characters dotenv treats specially
     */
    public static function formatValue(string|int|bool|null $value): string
    {
        if ($value === null) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) $value;
        if ($value === '') {
            return '';
        }
        if (preg_match('/[\s#"\'\\\\$=]/', $value)) {
            // Single quotes are taken literally by dotenv (no ${} expansion)
            if (!str_contains($value, "'")) {
                return "'" . $value . "'";
            }
            if (str_contains($value, '$')) {
                throw new RuntimeException('A .env value can\'t contain both \' and $. Choose a different value.');
            }
            return '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], $value) . '"';
        }

        return $value;
    }
}
