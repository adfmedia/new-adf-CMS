<?php

namespace App\Install;

use Adm\Models\AdmUser;
use Adm\Models\AdmUserGroup;
use App\Models\InstalledModule;
use App\Modules\ModuleManager;
use App\Modules\ModuleRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * The part of installing that needs Laravel: runs once .env exists.
 * Used by cms:install:finish, which both the CLI installer and the
 * /install wizard call.
 */
class InstallFinisher
{
    /** @var array<int, string> */
    protected array $log = [];

    public function __construct(
        protected ModuleRepository $Modules,
        protected ModuleManager $Manager
    ) {}

    /**
     * @param array{name: string, email: string, password: string} $admin
     * @param array<int, string> $optional_modules Optional modules to install as well as core
     * @return array<int, string> What was done
     */
    public function run(array $admin, array $optional_modules = []): array
    {
        $State = new InstallState(base_path());
        if ($State->isInstalled()) {
            throw new RuntimeException('This site is already installed (storage/installed.lock exists).');
        }

        $this->validateAdmin($admin);
        $core = $this->coreModules();
        $optional_modules = $this->checkOptional($optional_modules, $core);

        // 1. Only core modules take part in the first migrate. The paths are
        // given explicitly because the app booted before the registry existed,
        // so it registered every declared module's migrations.
        $this->writeModuleCache($core);
        $this->Modules->flush();
        $paths = ['database/migrations'];
        foreach ($core as $name) {
            if (is_dir(base_path('modules/' . $name . '/Database/migrations'))) {
                $paths[] = 'modules/' . $name . '/Database/migrations';
            }
        }
        $this->call('migrate', ['--force' => true, '--path' => $paths]);
        $this->log[] = 'Created the core database tables';

        // 2. The registry migration records every declared module; keep only core
        InstalledModule::query()->whereNotIn('name', $core)->delete();
        InstalledModule::query()->whereIn('name', $core)->update(['is_core' => true]);
        $this->Modules->rebuildCache();
        $this->log[] = 'Installed the core modules: ' . implode(', ', $core);

        // 3. Admin group and the first admin
        $this->call('db:seed', ['--force' => true]);
        $Group = AdmUserGroup::where('super_admin', true)->firstOrFail();
        AdmUser::updateOrCreate(
            ['email' => $admin['email']],
            [
                'name'              => $admin['name'],
                'password'          => Hash::make($admin['password']),
                'adm_user_group_id' => $Group->id,
            ]
        );
        $this->log[] = 'Created the admin ' . $admin['email'];

        // 4. Optional modules, through the same path as the admin screen
        foreach ($optional_modules as $name) {
            $this->Manager->install($name);
            $this->log[] = 'Installed ' . $name;
        }

        // 5. A starter home page, so the front end has something to show
        if ($this->createStarterHomePage((string) config('app.name', 'Our new site'))) {
            $this->log[] = 'Created a starter home page';
        }

        // 6. Assets and caches
        $this->call('cms:modules:publish');
        $this->Manager->refresh();

        $State->writeLock([
            'core'    => $core,
            'modules' => $optional_modules,
            'admin'   => $admin['email'],
        ]);
        $this->log[] = 'Locked the installer (storage/installed.lock)';

        return $this->log;
    }

    /**
     * Create a published "Home" page linked to the site root, with a
     * rich-text block when that module is installed. Does nothing if the
     * site already has a home page.
     *
     * @param string $site_name
     * @return bool Whether a page was created
     */
    public function createStarterHomePage(string $site_name): bool
    {
        if (!Schema::hasTable('pages') || !Schema::hasTable('urls')) {
            return false;
        }

        $route = 'controller=home&method=index';
        if (DB::table('urls')->where('system_page', 1)->where('route', $route)->exists()) {
            return false;
        }

        $language_id = (int) (DB::table('languages')->orderBy('id')->value('id') ?? 1);
        $page_id = (int) DB::table('pages')->max('page_id') + 1;
        $now = now();
        $page_class = \Modules\Pages\Models\Page::class;

        DB::table('pages')->insert([
            'page_id'     => $page_id,
            'language_id' => $language_id,
            'version'     => 1,
            'title'       => 'Home',
            'h1_title'    => $site_name,
            'status'      => 1,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        DB::table('urls')->insert([
            'url'              => '',
            'urlable_id'       => $page_id,
            'urlable_type'     => $page_class,
            'language_id'      => $language_id,
            'meta_title'       => $site_name,
            'meta_description' => null,
            'no_index'         => 0,
            'system_page'      => 1,
            'route'            => $route,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        // Starter content, editable in the page builder
        $wysiwyg = \Modules\WysiwygBlock\Blocks\Wysiwyg::class;
        if ($this->Modules->isEnabled('WysiwygBlock') && class_exists($wysiwyg)) {
            $block_id = DB::table('page_builder_blocks')->insertGetId([
                'morphable_id'    => $page_id,
                'morphable_type'  => $page_class,
                'morphable_field' => 'page_builder',
                'language_id'     => $language_id,
                'version'         => 1,
                'block_class'     => $wysiwyg,
                'sort_order'      => 0,
                'status'          => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            foreach ([
                'heading' => 'Welcome to ' . $site_name,
                'content' => '<p>This is your new site\'s starter home page. Sign in to the CMS and edit the Home page to build it with blocks.</p>',
            ] as $field => $value) {
                DB::table('page_builder_block_contents')->insert([
                    'page_builder_block_id' => $block_id,
                    'field'                 => $field,
                    'value'                 => $value,
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ]);
            }
        }

        return true;
    }

    /**
     * Names of the core modules on disk
     *
     * @return array<int, string>
     */
    public function coreModules(): array
    {
        $core = array_keys(array_filter(
            $this->Modules->allOnDisk(),
            fn ($Module) => $Module->isCore()
        ));

        if (!$core) {
            throw new RuntimeException('No core modules were found in modules/.');
        }

        return $core;
    }

    /**
     * Optional modules the installer can offer: on disk, not core, not dev-only
     *
     * @return array<string, string> name => description
     */
    public function optionalModules(): array
    {
        $optional = [];
        foreach ($this->Modules->allOnDisk() as $name => $Module) {
            if (!$Module->isCore() && empty($Module->manifest['dev_only'])) {
                $optional[$name] = (string) ($Module->manifest['description'] ?? '');
            }
        }

        return $optional;
    }

    /**
     * @param array<int, string> $requested
     * @param array<int, string> $core
     * @return array<int, string>
     */
    protected function checkOptional(array $requested, array $core): array
    {
        $available = array_keys($this->optionalModules());
        $requested = array_values(array_unique(array_filter(array_map('trim', $requested))));
        $requested = array_values(array_diff($requested, $core));

        $unknown = array_diff($requested, $available);
        if ($unknown) {
            throw new RuntimeException('These modules aren\'t available to install: ' . implode(', ', $unknown) . '.');
        }

        // Install anything a chosen module needs first
        $ordered = [];
        $visit = function (string $name) use (&$visit, &$ordered, $core) {
            if (in_array($name, $ordered, true) || in_array($name, $core, true)) {
                return;
            }
            $Module = $this->Modules->onDisk($name);
            foreach ((array) ($Module?->manifest['requires']['modules'] ?? []) as $required) {
                $visit($required);
            }
            $ordered[] = $name;
        };
        foreach ($requested as $name) {
            $visit($name);
        }

        return $ordered;
    }

    protected function validateAdmin(array $admin): void
    {
        $Validator = Validator::make($admin, [
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:191'],
            'password' => ['required', 'string', 'min:12'],
        ], [
            'password.min' => 'Use an admin password of at least 12 characters.',
        ]);

        if ($Validator->fails()) {
            throw new RuntimeException(implode(' ', $Validator->errors()->all()));
        }
    }

    protected function writeModuleCache(array $names): void
    {
        file_put_contents(
            ModuleRepository::cachePath(),
            '<?php return ' . var_export(['enabled' => array_values($names)], true) . ';' . PHP_EOL
        );
    }

    protected function call(string $command, array $arguments = []): void
    {
        $exit = Artisan::call($command, $arguments);
        if ($exit !== 0) {
            throw new RuntimeException($command . ' failed: ' . trim(Artisan::output()));
        }
    }
}
