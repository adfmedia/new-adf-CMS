<?php

namespace Adm\Http\Controllers;

use Adm\Models\ChangeLog;
use App\Models\InstalledModule;
use App\Modules\ModuleException;
use App\Modules\ModuleManager;
use App\Modules\ModuleZipInstaller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

/**
 * Admin > Settings > Modules: list, upload, enable, disable and uninstall
 * modules. Super admins only.
 */
class ModuleController extends AdmController
{
    /**
     * Route /adm/modules/{method?}/{id?}
     *
     * @param ?string $method
     * @param ?int $id
     * @return mixed
     */
    public function modules(?string $method = null, ?int $id = null): mixed
    {
        $User = Auth::guard('adm')->user();
        if (!$User || !$User->is_super_admin) {
            abort(403, 'Only super admins can manage modules.');
        }

        if ($method === null || $method === 'index') {
            return $this->index();
        }

        if (!request()->isMethod('post')) {
            return redirect()->route('adm.route', ['modules']);
        }

        return match ($method) {
            'upload'    => $this->upload(),
            'install'   => $this->install(),
            'enable'    => $this->runAction($id, fn (ModuleManager $M, InstalledModule $R) => $M->enable($R->name), 'enabled'),
            'disable'   => $this->runAction($id, fn (ModuleManager $M, InstalledModule $R) => $M->disable($R->name), 'disabled. Its data is kept'),
            'uninstall' => $this->uninstall($id),
            default     => abort(404),
        };
    }

    /**
     * The module list and upload form
     */
    protected function index(): View
    {
        $Manager = app(ModuleManager::class);
        $records = InstalledModule::query()->get()->keyBy('name');

        $rows = array_map(function (array $row) use ($records) {
            $row['record'] = $records->get($row['name']);
            return $row;
        }, $Manager->statusList());

        $activity = ChangeLog::withoutGlobalScope(\App\Models\Scopes\LanguageScope::class)
            ->where('changeable_type', InstalledModule::class)
            ->with('adm_user')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn (ChangeLog $Log) => [
                'module' => $records->firstWhere('id', $Log->changeable_id)?->name ?? 'Unknown',
                'action' => $Log->change_type,
                'user'   => $Log->adm_user?->name ?? 'Unknown',
                'date'   => $Log->created_at?->format('jS M Y, g:ia'),
            ]);

        return view('modules.index', [
            'title'          => 'Modules',
            'description'    => 'Add features to this site by uploading module zips, and switch them on or off. Core modules are always on.',
            'rows'           => $rows,
            'activity'       => $activity,
            'zip_available'  => class_exists(\ZipArchive::class),
            'max_upload'     => $this->maxUploadSize(),
            'breadcrumbs'    => [
                ['url' => route('adm.route'), 'title' => 'Dashboard'],
                ['url' => '#', 'title' => 'Modules'],
            ],
        ]);
    }

    /**
     * Install or upgrade from an uploaded zip
     */
    protected function upload(): RedirectResponse
    {
        $request = request();
        $max_kb = (int) floor(min(ModuleZipInstaller::MAX_ZIP_BYTES, $this->maxUploadSize()) / 1024);

        $request->validate([
            'module_zip' => ['required', 'file', 'mimes:zip', 'max:' . $max_kb],
        ], [
            'module_zip.required' => 'Choose a module zip to upload.',
            'module_zip.mimes'    => 'The module must be a .zip file.',
            'module_zip.max'      => 'The zip is too big to upload here.',
        ]);

        $dir = storage_path('app/module-uploads');
        File::ensureDirectoryExists($dir);
        $stored = $request->file('module_zip')->move($dir, Str::uuid() . '.zip');

        try {
            $result = app(ModuleZipInstaller::class)->installFromZip(
                $stored->getPathname(),
                $request->boolean('allow_reinstall')
            );
        } catch (ModuleException $e) {
            return $this->back('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);
            return $this->back('error', 'The module could not be installed: ' . $e->getMessage());
        } finally {
            @unlink($stored->getPathname());
        }

        $Record = $result['record'];
        $message = $result['action'] === 'upgraded'
            ? $Record->name . ' upgraded from ' . $result['from'] . ' to ' . $Record->version . '.'
            : $Record->name . ' ' . $Record->version . ' installed and enabled.';

        return $this->back('success', $message);
    }

    /**
     * Install a module whose folder is already on the server
     */
    protected function install(): RedirectResponse
    {
        $name = (string) request()->input('name');

        try {
            $Record = app(ModuleManager::class)->install($name);
        } catch (ModuleException $e) {
            return $this->back('error', $e->getMessage());
        }

        return $this->back('success', $Record->name . ' ' . $Record->version . ' installed and enabled.');
    }

    /**
     * Uninstall, keeping data and files unless told otherwise
     */
    protected function uninstall(?int $id): RedirectResponse
    {
        $Record = $this->findRecord($id);
        $delete_data = request()->boolean('delete_data');
        $remove_files = request()->boolean('remove_files');

        if ($delete_data && request()->input('confirm_name') !== $Record->name) {
            return $this->back('error', 'To delete ' . $Record->name . '\'s data, type its name to confirm.');
        }

        try {
            app(ModuleManager::class)->uninstall($Record->name, $delete_data, $remove_files);
        } catch (ModuleException $e) {
            return $this->back('error', $e->getMessage());
        }

        return $this->back(
            'success',
            $Record->name . ' uninstalled. ' . ($delete_data ? 'Its data was deleted.' : 'Its data was kept, so installing it again brings it back.')
        );
    }

    /**
     * Run a simple action against a registry record
     */
    protected function runAction(?int $id, callable $action, string $done): RedirectResponse
    {
        $Record = $this->findRecord($id);

        try {
            $action(app(ModuleManager::class), $Record);
        } catch (ModuleException $e) {
            return $this->back('error', $e->getMessage());
        }

        return $this->back('success', $Record->name . ' ' . $done . '.');
    }

    protected function findRecord(?int $id): InstalledModule
    {
        $Record = $id ? InstalledModule::find($id) : null;
        if (!$Record) {
            abort(404);
        }

        return $Record;
    }

    protected function back(string $type, string $message): RedirectResponse
    {
        return redirect()->route('adm.route', ['modules'])->with($type, $message);
    }

    /**
     * The largest upload PHP allows here, in bytes
     */
    protected function maxUploadSize(): int
    {
        $to_bytes = function (string $value): int {
            $value = trim($value);
            $number = (int) $value;
            return match (strtolower(substr($value, -1))) {
                'g'     => $number * 1024 * 1024 * 1024,
                'm'     => $number * 1024 * 1024,
                'k'     => $number * 1024,
                default => $number,
            };
        };

        $limits = array_filter([
            $to_bytes((string) ini_get('upload_max_filesize')),
            $to_bytes((string) ini_get('post_max_size')),
            ModuleZipInstaller::MAX_ZIP_BYTES,
        ]);

        return min($limits);
    }
}
