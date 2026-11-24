<?php

namespace App\Console\Commands;

use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use Illuminate\Console\Command;

class ModuleUninstall extends Command
{
    protected $signature = 'cms:module:uninstall
        {name : Module name, e.g. News}
        {--delete-data : Also roll back its migrations, dropping its tables}
        {--force : Don\'t ask for confirmation}';

    protected $description = 'Uninstall a module. Its data is kept unless --delete-data is given';

    public function handle(ModuleManager $Manager): int
    {
        $name = $this->argument('name');
        $delete = (bool) $this->option('delete-data');

        if ($delete && !$this->option('force')
            && !$this->confirm('This drops every table ' . $name . ' created, and its data can\'t be recovered. Continue?')) {
            $this->line('Cancelled.');
            return self::FAILURE;
        }

        try {
            $Record = $Manager->uninstall($name, $delete);
        } catch (ModuleException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info($Record->name . ' uninstalled. ' . ($delete ? 'Its data was deleted.' : 'Its data was kept, so installing it again restores it.'));
        return self::SUCCESS;
    }
}
