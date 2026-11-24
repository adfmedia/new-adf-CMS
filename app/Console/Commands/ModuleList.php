<?php

namespace App\Console\Commands;

use App\Modules\ModuleManager;
use App\Modules\ModuleRepository;
use Illuminate\Console\Command;

class ModuleList extends Command
{
    protected $signature = 'cms:module:list';

    protected $description = 'List every module on disk and its status in the registry';

    public function handle(ModuleManager $Manager, ModuleRepository $Modules): int
    {
        $rows = array_map(fn ($row) => [
            $row['name'],
            $row['version'] ?? '-',
            $row['installed'] ?? '-',
            $row['core'] ? 'yes' : '',
            $row['status'],
            $row['error'] ? mb_strimwidth($row['error'], 0, 60, '...') : '',
        ], $Manager->statusList());

        $this->table(['Module', 'Files', 'Installed', 'Core', 'Status', 'Last error'], $rows);

        $Modules->flush();
        $Modules->enabledNames();
        $this->line('Enabled list read from: ' . $Modules->source());

        return self::SUCCESS;
    }
}
