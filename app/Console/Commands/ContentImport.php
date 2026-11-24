<?php

namespace App\Console\Commands;

use Adm\Library\Import\AbstractImporter;
use App\Helpers\ClassFinder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ContentImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature    = 'content:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description  = 'Import content into the system';

    /**
     * Local array containing title => class mapping
     * 
     * @var array
     */
    protected $class_map    = [];

    /**
     * Local array containing input options
     * 
     * @var array
     */
    protected $options      = [];

    /**
     * Execute the command
     * 
     * @return void
     */
    public function handle(): void
    {
        $this->mapClasses();

        $resource = $this->choice(
            'Which type of content would you like to import?',
            $this->options,
            -1
        );

        if (!isset($this->class_map[$resource])) {
            $this->error('The resource "' . $resource . '" was not found.');
            exit;
        }

        $import_type = $this->choice(
            'Which import command would you like to perform?',
            ['Import', 'Map source data', 'Post-process source data']
        );

        config(['database.cache' => false]);

        /** @var AbstractImporter */
        $Lib = new $this->class_map[$resource]();
        $Lib->setConsoleCommand($this);

        switch ($import_type) {
            case 'Import':
                $Lib->importData();
                break;
            case 'Map source data':
                $Lib->mapSourceData();
                break;
            case 'Post-process source data':
                $Lib->postProcessMappedSourceData();
                break;
        }

        Cache::flush();
    }

    /**
     * Map all import resource classes into command options & class
     * map array, ready for use
     * 
     * @return void
     */
    protected function mapClasses(): void
    {
        $classes = ClassFinder::getClassesFromDirectory(
            base_path('adm/Library/Import/Resources'), 
            'Adm\Library\Import\Resources'
        );

        foreach ($classes as $ImportClass) {
            if (!$ImportClass instanceof AbstractImporter) {
                $this->error(get_class($ImportClass) . ' does not extend the abstract importer.');
                exit;
            }

            if (empty($ImportClass->resource_label)) {
                $this->error(get_class($ImportClass) . ' does not have a resource label.');
                exit;
            }

            $this->class_map[$ImportClass->resource_label] = $ImportClass;
            $this->options[] = $ImportClass->resource_label;
        }
    }
}