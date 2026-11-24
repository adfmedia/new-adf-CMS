<?php

namespace App\Console\Commands;

use App\Modules\ModuleRepository;
use Illuminate\Console\Command;

class ModulesCache extends Command
{
    protected $signature = 'cms:modules:cache';

    protected $description = 'Rebuild bootstrap/cache/modules.php from the module registry';

    public function handle(ModuleRepository $Modules): int
    {
        $Modules->rebuildCache();
        $names = $Modules->enabledNames();

        $this->info('Enabled modules (' . $Modules->source() . '): ' . implode(', ', $names));
        return self::SUCCESS;
    }
}
