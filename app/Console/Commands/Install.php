<?php

namespace App\Console\Commands;

use App\Install\DatabaseCreator;
use App\Install\EnvWriter;
use App\Install\InstallState;
use App\Install\Requirements;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Install the CMS from the command line, doing everything the /install
 * wizard does. Missing options are asked for, unless --no-interaction.
 *
 * Example:
 *   php artisan cms:install --site-name="Acme" --url=https://acme.test \
 *     --db-root-user=root --admin-email=me@acme.test --modules=News,Events
 */
class Install extends Command
{
    protected $signature = 'cms:install
        {--site-name= : Site name}
        {--url= : Site URL, e.g. https://www.example.com}
        {--app-env=production : Environment: local, staging or production}
        {--timezone=Europe/London : Timezone}
        {--mail-from= : Address emails are sent from}
        {--db-host=127.0.0.1 : MySQL host}
        {--db-port=3306 : MySQL port}
        {--db-name= : Database name (suggested from the site name)}
        {--db-user= : Database user (suggested from the site name)}
        {--db-password= : Database user\'s password (generated if empty and creating)}
        {--db-root-user= : A MySQL login that can create databases and users. Leave empty to use an existing database}
        {--db-root-password= : That login\'s password (or CMS_INSTALL_DB_ROOT_PASSWORD)}
        {--admin-name= : First admin\'s name}
        {--admin-email= : First admin\'s email}
        {--admin-password= : First admin\'s password (or CMS_INSTALL_ADMIN_PASSWORD)}
        {--modules= : Optional modules to install, comma separated}';

    protected $description = 'Install the CMS: check the server, create the database, write .env, set up tables, the first admin and modules';

    public function handle(): int
    {
        $State = new InstallState(base_path());
        if ($State->isInstalled()) {
            $this->error('This site is already installed (storage/installed.lock exists).');
            return self::FAILURE;
        }
        if ($State->hasEnv()) {
            $this->error('A .env file already exists. Remove it to install from scratch, or run php artisan cms:install:finish to finish an install that already has one.');
            return self::FAILURE;
        }

        // 1. Server
        $Requirements = new Requirements(base_path());
        if (!$Requirements->passes()) {
            $this->error('This server is missing things the CMS needs:');
            foreach ($Requirements->failures() as $failure) {
                $this->line('  - ' . $failure);
            }
            return self::FAILURE;
        }
        $this->info('Server checks passed.');

        // 2. Answers
        $site_name = $this->value('site-name', 'Site name');
        $url = rtrim($this->value('url', 'Site URL', 'http://localhost'), '/');
        $suggested = DatabaseCreator::suggest($site_name);

        $db = [
            'host'     => (string) $this->option('db-host'),
            'port'     => (int) $this->option('db-port'),
            'database' => $this->value('db-name', 'Database name', $suggested['database']),
            'username' => $this->value('db-user', 'Database user', $suggested['username']),
            'password' => (string) ($this->option('db-password') ?: ''),
        ];
        $root_user = (string) ($this->option('db-root-user') ?? '');
        if ($root_user === '' && $this->input->isInteractive()) {
            $root_user = (string) $this->ask('MySQL login that can create databases and users (leave empty to use an existing database)', '');
        }

        $admin_email = $this->value('admin-email', 'First admin\'s email');
        $admin_name = $this->value('admin-name', 'First admin\'s name', 'Admin');
        $admin_password = $this->option('admin-password') ?: getenv('CMS_INSTALL_ADMIN_PASSWORD') ?: '';
        if ($admin_password === '' && $this->input->isInteractive()) {
            $admin_password = (string) $this->secret('First admin\'s password (at least 12 characters)');
        }

        // 3. Database
        try {
            $Creator = new DatabaseCreator();
            if ($root_user !== '') {
                $root_password = $this->option('db-root-password') ?? getenv('CMS_INSTALL_DB_ROOT_PASSWORD') ?: '';
                if ($root_password === '' && $this->input->isInteractive()) {
                    $root_password = (string) $this->secret('Password for ' . $root_user . ' (leave empty if none)');
                }
                if ($db['password'] === '') {
                    $db['password'] = $suggested['password'];
                }
                foreach ($Creator->create($db['host'], $db['port'], $root_user, (string) $root_password, $db['database'], $db['username'], $db['password']) as $line) {
                    $this->line('  ' . $line);
                }
            } else {
                if ($db['password'] === '' && $this->input->isInteractive()) {
                    $db['password'] = (string) $this->secret('Password for database user ' . $db['username']);
                }
                $Creator->checkExisting($db['host'], $db['port'], $db['username'], $db['password'], $db['database']);
                $this->line('  Connected to the existing database ' . $db['database']);
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        // 4. .env
        try {
            (new EnvWriter(base_path()))->write([
                'APP_NAME'          => $site_name,
                'APP_ENV'           => (string) $this->option('app-env'),
                'APP_KEY'           => EnvWriter::generateAppKey(),
                'APP_DEBUG'         => $this->option('app-env') !== 'production',
                'APP_URL'           => $url,
                'APP_TIMEZONE'      => (string) $this->option('timezone'),
                'DB_HOST'           => $db['host'],
                'DB_PORT'           => $db['port'],
                'DB_DATABASE'       => $db['database'],
                'DB_USERNAME'       => $db['username'],
                'DB_PASSWORD'       => $db['password'],
                'MAIL_FROM_ADDRESS' => $this->option('mail-from') ?: $admin_email,
                'LOG_LEVEL'         => $this->option('app-env') === 'production' ? 'error' : 'debug',
            ]);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
        $this->info('Wrote .env');

        // 5. The rest runs in a fresh process, so Laravel loads the new .env
        $Process = new Process(
            [PHP_BINARY, base_path('artisan'), 'cms:install:finish',
                '--admin-name=' . $admin_name,
                '--admin-email=' . $admin_email,
                '--modules=' . (string) $this->option('modules'),
                '--no-interaction'],
            base_path(),
            ['CMS_INSTALL_ADMIN_PASSWORD' => $admin_password],
            null,
            600
        );
        $Process->run(fn ($type, $buffer) => $this->output->write($buffer));

        if (!$Process->isSuccessful()) {
            $this->error('Finishing the install failed. Fix the problem above, then run: php artisan cms:install:finish');
            return self::FAILURE;
        }

        $this->info('Done. Sign in at ' . $url . '/adm');
        return self::SUCCESS;
    }

    /**
     * An option's value, asked for when missing and interactive
     */
    protected function value(string $option, string $question, ?string $default = null): string
    {
        $value = $this->option($option);
        if (($value === null || $value === '') && $this->input->isInteractive()) {
            $value = $this->ask($question, $default);
        }
        $value = (string) ($value ?? $default ?? '');

        if ($value === '') {
            throw new \InvalidArgumentException('--' . $option . ' is needed.');
        }

        return $value;
    }
}
