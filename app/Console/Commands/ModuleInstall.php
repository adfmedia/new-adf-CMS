<?php

namespace App\Console\Commands;

use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModuleInstall extends Command
{
    protected $signature = 'cms:module:install
        {name : Module folder name in modules/, e.g. News}
        {--no-seed : Skip the module\'s seeder on a first install}
        {--dev : Allow a development-only module outside local/testing}';

    protected $description = 'Install a module from modules/<name>: migrate, seed, publish assets, enable';

    public function handle(ModuleManager $Manager): int
    {
        try {
            $Record = $Manager->install($this->argument('name'), !$this->option('no-seed'), (bool) $this->option('dev'));
        } catch (ModuleException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info($Record->name . ' ' . $Record->version . ' installed and enabled.');
        return self::SUCCESS;
    }
}
