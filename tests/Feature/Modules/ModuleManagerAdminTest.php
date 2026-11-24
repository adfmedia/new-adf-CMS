<?php

namespace Tests\Feature\Modules;

use Adm\Models\AdmUser;
use Adm\Models\AdmUserGroup;
use Adm\Models\ChangeLog;
use App\Models\InstalledModule;
use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use App\Modules\ModuleRepository;
use App\Modules\ModuleZipInstaller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use ZipArchive;

/**
 * Phase 3 checks: the Admin > Modules screen and installing from zips.
 *
 * These run against the database in .env and change module state, then put
 * it back. The zip tests need the PHP zip extension; without it they skip.
 */
class ModuleManagerAdminTest extends TestCase
{
    protected string $base = 'https://www.new-adf-cms.laragon';

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('modules')) {
            $this->markTestSkipped('Run php artisan migrate first: the modules table does not exist.');
        }

        // The app's custom environment detection means Laravel doesn't know
        // it's running tests, so switch off the CSRF check for these posts
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }

    protected function superAdmin(): AdmUser
    {
        $User = AdmUser::query()->whereHas('group', fn ($q) => $q->where('super_admin', true))->first();
        if (!$User) {
            $this->markTestSkipped('Needs a super admin user (php artisan db:seed).');
        }

        return $User;
    }

    protected function requireZip(): void
    {
        if (!class_exists(ZipArchive::class)) {
            $this->markTestSkipped('The PHP zip extension is not enabled.');
        }
    }

    /**
     * Build a zip from [path => contents]
     */
    protected function makeZip(array $files): string
    {
        $dir = storage_path('framework/testing');
        File::ensureDirectoryExists($dir);
        $path = $dir . '/' . uniqid('module-', true) . '.zip';

        $Zip = new ZipArchive();
        $Zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($files as $name => $contents) {
            $Zip->addFromString($name, $contents);
        }
        $Zip->close();

        return $path;
    }

    protected function manifest(string $name, string $version = '1.0.0'): string
    {
        return json_encode(['name' => $name, 'version' => $version, 'core' => false]);
    }

    /* ---------------------------------------------------------------- */

    public function test_super_admin_sees_the_modules_screen(): void
    {
        $this->actingAs($this->superAdmin(), 'adm');

        $res = $this->get($this->base . '/adm/modules');

        $res->assertOk();
        $res->assertSee('Upload a module');
        $res->assertSee('Testimonials');
        $res->assertSee('Core');
    }

    public function test_other_admins_cannot_manage_modules(): void
    {
        $Group = AdmUserGroup::create(['title' => 'Module test editors', 'permissions' => ['modules'], 'super_admin' => false, 'active' => true]);
        $User = AdmUser::create([
            'name'              => 'Module Test Editor',
            'email'             => 'module-test-' . uniqid() . '@example.test',
            'password'          => bcrypt(str()->random(32)),
            'adm_user_group_id' => $Group->id,
        ]);

        try {
            $this->actingAs($User, 'adm');
            $this->get($this->base . '/adm/modules')->assertForbidden();
            $this->post($this->base . '/adm/modules/disable/1')->assertForbidden();
        } finally {
            $User->delete();
            $Group->delete();
        }
    }

    public function test_disable_and_enable_from_the_admin_are_logged(): void
    {
        $Record = InstalledModule::where('name', 'Testimonials')->first();
        if (!$Record || !$Record->isEnabled()) {
            $this->markTestSkipped('Testimonials must be installed and enabled.');
        }

        $this->actingAs($this->superAdmin(), 'adm');
        $logs_before = ChangeLog::withoutGlobalScope(\App\Models\Scopes\LanguageScope::class)->where('changeable_type', InstalledModule::class)->count();

        try {
            $this->post($this->base . '/adm/modules/disable/' . $Record->id)
                ->assertRedirect()
                ->assertSessionHas('success');
            $this->assertSame(InstalledModule::STATUS_DISABLED, $Record->fresh()->status);

            $this->post($this->base . '/adm/modules/enable/' . $Record->id)
                ->assertSessionHas('success');
            $this->assertSame(InstalledModule::STATUS_ENABLED, $Record->fresh()->status);

            $this->assertSame($logs_before + 2, ChangeLog::withoutGlobalScope(\App\Models\Scopes\LanguageScope::class)->where('changeable_type', InstalledModule::class)->count());
        } finally {
            if ($Record->fresh()->status !== InstalledModule::STATUS_ENABLED) {
                app(ModuleManager::class)->enable('Testimonials');
            }
            ChangeLog::withoutGlobalScope(\App\Models\Scopes\LanguageScope::class)->where('changeable_type', InstalledModule::class)->where('changeable_id', $Record->id)
                ->where('created_at', '>=', now()->subMinutes(5))->delete();
        }
    }

    public function test_core_modules_cannot_be_uninstalled_from_the_admin(): void
    {
        $this->actingAs($this->superAdmin(), 'adm');
        $Pages = InstalledModule::where('name', 'Pages')->firstOrFail();

        $this->post($this->base . '/adm/modules/uninstall/' . $Pages->id)->assertSessionHas('error');
        $this->assertSame(InstalledModule::STATUS_ENABLED, $Pages->fresh()->status);
    }

    public function test_deleting_data_needs_the_name_typed(): void
    {
        $this->actingAs($this->superAdmin(), 'adm');
        $Record = InstalledModule::where('name', 'Testimonials')->firstOrFail();

        $this->post($this->base . '/adm/modules/uninstall/' . $Record->id, ['delete_data' => 1, 'confirm_name' => 'wrong'])
            ->assertSessionHas('error');
        $this->assertTrue(Schema::hasTable('testimonials'));
        $this->assertSame(InstalledModule::STATUS_ENABLED, $Record->fresh()->status);
    }

    #[DataProvider('unsafeZips')]
    public function test_unsafe_zips_are_rejected_before_extracting(array $files, string $expected): void
    {
        $this->requireZip();
        $path = $this->makeZip($files);

        try {
            app(ModuleZipInstaller::class)->installFromZip($path);
            $this->fail('The zip should have been rejected');
        } catch (ModuleException $e) {
            $this->assertStringContainsString($expected, $e->getMessage());
        } finally {
            @unlink($path);
        }

        $this->assertDirectoryDoesNotExist(base_path('modules/Evil'));
        $this->assertFileDoesNotExist(base_path('evil.php'));
    }

    public static function unsafeZips(): array
    {
        $ok = json_encode(['name' => 'Evil', 'version' => '1.0.0']);

        return [
            'path traversal'   => [['Evil/module.json' => $ok, 'Evil/../../evil.php' => '<?php'], 'unsafe path'],
            'two folders'      => [['Evil/module.json' => $ok, 'Other/x.php' => '<?php'], 'single module folder'],
            'php in Public'    => [['Evil/module.json' => $ok, 'Evil/Public/shell.php' => '<?php'], 'Public/'],
            'htaccess'         => [['Evil/module.json' => $ok, 'Evil/Public/.htaccess' => 'x'], 'Hidden files'],
            'phar'             => [['Evil/module.json' => $ok, 'Evil/App/x.phar' => 'x'], 'not allowed'],
            'name mismatch'    => [['Evil/module.json' => json_encode(['name' => 'Good', 'version' => '1.0.0'])], 'does not match'],
            'no manifest'      => [['Evil/App/x.php' => '<?php'], 'module.json'],
            'bad folder name'  => [['evil/module.json' => json_encode(['name' => 'evil', 'version' => '1.0.0'])], 'capital'],
        ];
    }

    public function test_checksum_mismatch_is_rejected(): void
    {
        $this->requireZip();
        $path = $this->makeZip([
            'Evil/module.json'      => json_encode(['name' => 'Evil', 'version' => '1.0.0', 'checksum' => str_repeat('a', 64)]),
            'Evil/Config/Thing.php' => '<?php',
        ]);

        try {
            $this->expectException(ModuleException::class);
            $this->expectExceptionMessage('checksum');
            app(ModuleZipInstaller::class)->installFromZip($path);
        } finally {
            @unlink($path);
            $this->assertDirectoryDoesNotExist(base_path('modules/Evil'));
        }
    }

    /**
     * The full journey from the plan: package Faqs, uninstall it keeping its
     * data and removing its files, upload the zip in the admin, and get the
     * same data back.
     */
    public function test_package_uninstall_and_reupload_keeps_data(): void
    {
        $this->requireZip();
        $Record = InstalledModule::where('name', 'Faqs')->first();
        if (!$Record || !$Record->isEnabled()) {
            $this->markTestSkipped('Faqs must be installed and enabled.');
        }

        $Manager = app(ModuleManager::class);
        $this->actingAs($this->superAdmin(), 'adm');

        $manifest_path = base_path('modules/Faqs/module.json');
        $original_manifest = file_get_contents($manifest_path);
        $marker = 'Module round trip ' . uniqid();
        $faq_id = (int) DB::table('faqs')->max('faq_id') + 1000;
        DB::table('faqs')->insert([
            'faq_id'      => $faq_id,
            'language_id' => 1,
            'title'       => $marker,
            'question'    => $marker,
            'answer'      => '<p>Answer</p>',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $zip = $Manager->package('Faqs', storage_path('framework/testing'));

        try {
            $this->post($this->base . '/adm/modules/uninstall/' . $Record->id, ['remove_files' => 1])
                ->assertSessionHas('success');
            $this->assertSame(InstalledModule::STATUS_UNINSTALLED, $Record->fresh()->status);
            $this->assertDirectoryDoesNotExist(base_path('modules/Faqs'));
            app(ModuleRepository::class)->flush();
            $this->assertFalse(app(ModuleRepository::class)->isEnabled('Faqs'));

            $upload = new UploadedFile($zip, 'Faqs-1.0.0.zip', 'application/zip', null, true);
            $this->post($this->base . '/adm/modules/upload', ['module_zip' => $upload])
                ->assertSessionHas('success');

            $this->assertSame(InstalledModule::STATUS_ENABLED, $Record->fresh()->status);
            $this->assertDirectoryExists(base_path('modules/Faqs'));
            $this->assertSame($marker, DB::table('faqs')->where('faq_id', $faq_id)->value('question'));
        } finally {
            // Put Faqs back however the test ended
            if (!is_dir(base_path('modules/Faqs'))) {
                $backups = glob(base_path('modules/.backup/Faqs-*')) ?: [];
                if ($backups) {
                    rename(end($backups), base_path('modules/Faqs'));
                }
            }
            if ($Record->fresh()->status !== InstalledModule::STATUS_ENABLED) {
                $Record->fresh()->status === InstalledModule::STATUS_DISABLED
                    ? $Manager->enable('Faqs')
                    : $Manager->install('Faqs', false);
            }
            // The zip's module.json carries a checksum; put the source copy back
            file_put_contents($manifest_path, $original_manifest);
            DB::table('faqs')->where('faq_id', $faq_id)->delete();
            foreach (glob(base_path('modules/.backup/Faqs-*')) ?: [] as $dir) {
                File::deleteDirectory($dir);
            }
            @unlink($zip);
            @rmdir(base_path('modules/.backup'));
        }
    }
}
