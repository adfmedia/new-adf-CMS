<?php

namespace App\Console\Commands;

use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModulePackage extends Command
{
    protected $signature = 'cms:module:package
        {name : Module name, e.g. News}
        {--output= : Folder to write the zip to (default storage/app/module-packages)}';

    protected $description = 'Build an uploadable <Name>-<version>.zip of a module, with a checksum';

    public function handle(ModuleManager $Manager): int
    {
        try {
            $path = $Manager->package($this->argument('name'), $this->option('output') ?: null);
        } catch (ModuleException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info('Packaged to ' . $path);
        return self::SUCCESS;
    }
}
