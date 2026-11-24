<?php

namespace Tests\Feature\Modules;

use App\Models\InstalledModule;
use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use App\Modules\ModuleRepository;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

/**
 * Phase 2 checks: the module registry, its cache file and the ModuleManager.
 *
 * The enable/disable tests change the registry in the database they run
 * against, and put it back as it was afterwards.
 */
class ModuleRegistryTest extends TestCase
{
    protected function requireRegistry(): void
    {
        if (!Schema::hasTable('modules')) {
            $this->markTestSkipped('Run php artisan migrate first: the modules table does not exist.');
        }
    }

    public function test_version_constraints(): void
    {
        $this->assertTrue(ModuleManager::satisfies('8.3.4', '>=8.3'));
        $this->assertFalse(ModuleManager::satisfies('8.2.9', '>=8.3'));
        $this->assertTrue(ModuleManager::satisfies('1.4.0', '^1.2'));
        $this->assertFalse(ModuleManager::satisfies('2.0.0', '^1.2'));
    }

    public function test_module_name_is_read_from_a_class(): void
    {
        $this->assertSame('News', ModuleRepository::moduleNameForClass('Modules\\News\\Models\\NewsArticle'));
        $this->assertNull(ModuleRepository::moduleNameForClass('App\\Models\\Url'));
    }

    public function test_enabled_list_is_read_from_the_cache_file(): void
    {
        $path = ModuleRepository::cachePath();
        $backup = is_file($path) ? file_get_contents($path) : null;

        try {
            file_put_contents($path, '<?php return ' . var_export(['enabled' => ['Pages', 'Forms']], true) . ';');
            $Modules = new ModuleRepository();

            $this->assertSame(['Pages', 'Forms'], $Modules->enabledNames());
            $this->assertSame('cache', $Modules->source());
            $this->assertTrue($Modules->isEnabled('Forms'));
            $this->assertFalse($Modules->isEnabled('News'));
        } finally {
            $backup === null ? @unlink($path) : file_put_contents($path, $backup);
        }
    }

    public function test_registry_holds_the_declared_modules(): void
    {
        $this->requireRegistry();

        foreach (['Pages', 'GlobalBlocks', 'Forms', 'Search'] as $name) {
            $Record = InstalledModule::where('name', $name)->first();
            $this->assertNotNull($Record, $name . ' is not in the registry');
            $this->assertTrue($Record->is_core, $name . ' should be core');
        }
    }

    public function test_config_available_reflects_the_registry(): void
    {
        $this->requireRegistry();

        $enabled = InstalledModule::where('status', InstalledModule::STATUS_ENABLED)->orderBy('name')->pluck('name')->all();
        $available = config('modules.available');
        sort($available);

        $this->assertSame($enabled, $available);
        $this->assertIsArray(config('modules.declared'));
    }

    public function test_core_modules_cannot_be_disabled_or_uninstalled(): void
    {
        $this->requireRegistry();
        $Manager = app(ModuleManager::class);

        $this->expectException(ModuleException::class);
        $Manager->disable('Pages');
    }

    public function test_core_modules_cannot_be_uninstalled(): void
    {
        $this->requireRegistry();

        $this->expectException(ModuleException::class);
        app(ModuleManager::class)->uninstall('Forms');
    }

    public function test_a_module_can_be_disabled_and_enabled_again(): void
    {
        $this->requireRegistry();
        $Record = InstalledModule::where('name', 'Testimonials')->first();
        if (!$Record || !$Record->isEnabled()) {
            $this->markTestSkipped('Testimonials must be installed and enabled for this test.');
        }

        $Manager = app(ModuleManager::class);
        $Modules = app(ModuleRepository::class);

        try {
            $Manager->disable('Testimonials');
            $Modules->flush();
            $this->assertSame(InstalledModule::STATUS_DISABLED, $Record->fresh()->status);
            $this->assertFalse($Modules->isEnabled('Testimonials'));
            $this->assertTrue(Schema::hasTable('testimonials'), 'Disabling must keep the module\'s tables');
        } finally {
            if ($Record->fresh()->status !== InstalledModule::STATUS_ENABLED) {
                $Manager->enable('Testimonials');
            }
        }

        $Modules->flush();
        $this->assertTrue($Modules->isEnabled('Testimonials'));
    }

    public function test_disabled_module_pages_and_blocks_are_hidden(): void
    {
        $this->requireRegistry();
        $Record = InstalledModule::where('name', 'Testimonials')->first();
        if (!$Record || !$Record->isEnabled()) {
            $this->markTestSkipped('Testimonials must be installed and enabled for this test.');
        }

        $Manager = app(ModuleManager::class);
        try {
            $Manager->disable('Testimonials');
            app(ModuleRepository::class)->flush();

            $Block = new \App\Models\PageBuilderBlock();
            $Block->block_class = \Modules\Testimonials\Blocks\Testimonials::class;
            $this->assertNull($Block->view_name, 'A block from a disabled module should be skipped');
        } finally {
            if ($Record->fresh()->status !== InstalledModule::STATUS_ENABLED) {
                $Manager->enable('Testimonials');
            }
        }
    }

    public function test_a_module_can_be_packaged_with_a_checksum(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('The PHP zip extension is not enabled.');
        }

        $dir = storage_path('framework/testing/module-packages');
        $zip_path = app(ModuleManager::class)->package('Faqs', $dir);

        try {
            $this->assertFileExists($zip_path);

            $Zip = new ZipArchive();
            $this->assertTrue($Zip->open($zip_path) === true);
            $manifest = json_decode($Zip->getFromName('Faqs/module.json'), true);
            $this->assertSame('Faqs', $manifest['name']);
            $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $manifest['checksum']);
            $this->assertNotFalse($Zip->locateName('Faqs/Models/Faq.php') ?: $Zip->locateName('Faqs/Config/FaqsConfig.php'));
            $Zip->close();
        } finally {
            @unlink($zip_path);
        }
    }
}
