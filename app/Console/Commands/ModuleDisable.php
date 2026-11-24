<?php

namespace App\Console\Commands;

use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModuleDisable extends Command
{
    protected $signature = 'cms:module:disable {name : Module name, e.g. News}';

    protected $description = 'Disable a module. Its files and data are kept';

    public function handle(ModuleManager $Manager): int
    {
        try {
            $Record = $Manager->disable($this->argument('name'));
        } catch (ModuleException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info($Record->name . ' disabled. Its data is kept.');
        return self::SUCCESS;
    }
}
