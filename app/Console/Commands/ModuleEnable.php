<?php

namespace App\Console\Commands;

use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModuleEnable extends Command
{
    protected $signature = 'cms:module:enable {name : Module name, e.g. News}';

    protected $description = 'Enable an installed module';

    public function handle(ModuleManager $Manager): int
    {
        try {
            $Record = $Manager->enable($this->argument('name'));
        } catch (ModuleException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info($Record->name . ' enabled.');
        return self::SUCCESS;
    }
}
