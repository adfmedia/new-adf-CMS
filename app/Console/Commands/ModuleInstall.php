<?php

namespace App\Console\Commands;

use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use App\Modules\ModuleZipInstaller;
use Illuminate\Console\Command;

class ModuleInstall extends Command
{
    protected $signature = 'cms:module:install
        {name : Module folder name in modules/ (e.g. News), or a path to a module zip}
        {--reinstall : With a zip, allow installing the same version again}
        {--no-seed : Skip the module\'s seeder on a first install}
        {--dev : Allow a development-only module outside local/testing}';

    protected $description = 'Install a module from modules/<name> or a zip: migrate, seed, publish assets, enable';

    public function handle(ModuleManager $Manager): int
    {
        $name = $this->argument('name');

        try {
            if (str_ends_with(strtolower($name), '.zip')) {
                $result = app(ModuleZipInstaller::class)->installFromZip($name, (bool) $this->option('reinstall'));
                $Record = $result['record'];
                $this->info($result['action'] === 'upgraded'
                    ? $Record->name . ' upgraded from ' . $result['from'] . ' to ' . $Record->version . '.'
                    : $Record->name . ' ' . $Record->version . ' installed and enabled.');
                return self::SUCCESS;
            }

            $Record = $Manager->install($name, !$this->option('no-seed'), (bool) $this->option('dev'));
        } catch (ModuleException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info($Record->name . ' ' . $Record->version . ' installed and enabled.');
        return self::SUCCESS;
    }
}
