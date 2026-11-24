<?php

namespace Tests\Feature\Modules;

use App\Helpers\ClassFinder;
use App\Modules\ModuleRepository;
use App\Models\PageBuilderBlock;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

/**
 * Phase 1 checks: every module is self-contained (manifest, views, assets)
 * and the site resolves module views through their namespaces.
 */
class ModuleStructureTest extends TestCase
{
    public function test_every_listed_module_has_a_valid_manifest(): void
    {
        foreach (config('modules.declared', config('modules.available')) as $name) {
            $path = base_path('modules/' . $name . '/module.json');
            $this->assertFileExists($path, $name . ' is missing module.json');

            $manifest = json_decode(file_get_contents($path), true);
            $this->assertIsArray($manifest, $name . ' module.json is not valid JSON');
            $this->assertSame($name, $manifest['name'] ?? null, $name . ' module.json name does not match its folder');
            $this->assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', $manifest['version'] ?? '', $name . ' needs a semantic version');
            $this->assertIsBool($manifest['core'] ?? null, $name . ' must say whether it is core');
        }
    }

    public function test_core_modules_are_marked_core(): void
    {
        $Modules = app(ModuleRepository::class);

        foreach (['Pages', 'GlobalBlocks', 'Forms', 'Search'] as $name) {
            $this->assertTrue($Modules->onDisk($name)?->isCore(), $name . ' should be core');
        }
        $this->assertFalse($Modules->onDisk('News')?->isCore(), 'News should be optional');
    }

    public function test_module_views_are_registered_under_their_namespaces(): void
    {
        $views = [
            'case-studies::listing', 'case-studies::article', 'case-studies::partials.ajax-tiles',
            'events::listing', 'events::event',
            'forms::templates.default', 'forms::emails.auto-response', 'forms::emails.internal', 'forms::emails.internal__plain',
            'news::listing', 'news::article', 'news::partials.ajax-tiles',
            'offices::listing', 'offices::office',
            'pages::home', 'pages::page',
            'search::results',
            'team-members::listing', 'team-members::team-member',
        ];

        foreach ($views as $view) {
            $this->assertTrue(View::exists($view), 'View ' . $view . ' is not found');
        }
    }

    public function test_old_core_view_folders_are_gone(): void
    {
        foreach (['case-studies', 'events', 'forms', 'news', 'offices', 'pages', 'search', 'team-members', 'emails/cms-form'] as $folder) {
            $this->assertDirectoryDoesNotExist(resource_path('views/' . $folder), 'resources/views/' . $folder . ' should have moved into its module');
        }
        $this->assertDirectoryDoesNotExist(resource_path('views/page-builder/blocks'));
        $this->assertDirectoryDoesNotExist(resource_path('images/block-previews'));
    }

    public function test_every_block_resolves_to_a_module_view(): void
    {
        foreach (ClassFinder::getPageBuilderBlockClasses() as $BlockRef) {
            $Block = new PageBuilderBlock();
            $Block->block_class = $BlockRef->getName();

            $this->assertNotNull($Block->view_name, $BlockRef->getName() . ' has no view');
            $this->assertTrue(View::exists($Block->view_name), $BlockRef->getName() . ' view ' . $Block->view_name . ' is not found');
        }
    }

    public function test_block_for_a_missing_class_is_skipped(): void
    {
        $Block = new PageBuilderBlock();
        $Block->block_class = 'Modules\\NotInstalled\\Blocks\\Ghost';

        $this->assertNull($Block->view_name);
    }

    public function test_every_block_preview_image_is_published(): void
    {
        $Modules = app(ModuleRepository::class);

        foreach (ClassFinder::getPageBuilderBlockClasses() as $BlockRef) {
            $preview = $BlockRef->getStaticPropertyValue('preview');
            if (empty($preview['module_asset'])) {
                continue;
            }

            $Module = $Modules->forClass($BlockRef->getName());
            $this->assertNotNull($Module);
            $this->assertFileExists($Module->subPath('Public/' . $preview['src']), 'Source preview missing for ' . $BlockRef->getName());
            $this->assertFileExists($Module->publicTargetPath() . '/' . $preview['src'], 'Run php artisan cms:modules:publish: preview not published for ' . $BlockRef->getName());
        }
    }

    public function test_module_migration_paths_are_found(): void
    {
        $paths = \App\Providers\ModuleServiceProvider::getModuleMigrationPaths();

        $this->assertContains('modules/News/Database/migrations', $paths);
        $this->assertContains('modules/Pages/Database/migrations', $paths);
    }
}
