<?php

namespace App\Console\Commands;

use App\Install\InstallFinisher;
use Illuminate\Console\Command;
use Throwable;

class InstallFinish extends Command
{
    protected $signature = 'cms:install:finish
        {--admin-name= : The first admin\'s name}
        {--admin-email= : The first admin\'s email}
        {--admin-password= : Prefer the CMS_INSTALL_ADMIN_PASSWORD environment variable, which stays out of shell history}
        {--modules= : Optional modules to install, comma separated, e.g. News,Events}';

    protected $description = 'Second half of installing, once .env exists: tables, core modules, first admin, optional modules, lock';

    public function handle(InstallFinisher $Finisher): int
    {
        $password = $this->option('admin-password') ?: getenv('CMS_INSTALL_ADMIN_PASSWORD') ?: '';
        $modules = array_filter(explode(',', (string) $this->option('modules')));

        try {
            $log = $Finisher->run([
                'name'     => (string) $this->option('admin-name'),
                'email'    => (string) $this->option('admin-email'),
                'password' => (string) $password,
            ], $modules);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        foreach ($log as $line) {
            $this->line('  ' . $line);
        }
        $this->info('Installed.');

        return self::SUCCESS;
    }
}
