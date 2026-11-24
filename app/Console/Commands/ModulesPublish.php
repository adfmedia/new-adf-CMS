<?php

namespace App\Console\Commands;

use App\Modules\ModuleRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ModulesPublish extends Command
{
    /**
     * @var string
     */
    protected $signature = 'cms:modules:publish {module? : Only publish this module}';

    /**
     * @var string
     */
    protected $description = 'Copy each enabled module\'s Public folder to public/modules/<namespace>';

    /**
     * Execute the console command.
     *
     * @param ModuleRepository $Modules
     * @return int
     */
    public function handle(ModuleRepository $Modules): int
    {
        $only = $this->argument('module');
        $modules = $Modules->enabled();

        if ($only !== null) {
            if (!isset($modules[$only])) {
                $this->error('Module ' . $only . ' is not enabled.');
                return self::FAILURE;
            }
            $modules = [$only => $modules[$only]];
        }

        foreach ($modules as $Module) {
            if (!is_dir($Module->publicSourcePath())) {
                continue;
            }

            File::deleteDirectory($Module->publicTargetPath());
            File::ensureDirectoryExists($Module->publicTargetPath());
            File::copyDirectory($Module->publicSourcePath(), $Module->publicTargetPath());

            $this->line('Published ' . $Module->name . ' to public/modules/' . $Module->viewNamespace());
        }

        return self::SUCCESS;
    }
}
