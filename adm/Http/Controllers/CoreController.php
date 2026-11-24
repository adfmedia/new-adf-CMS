<?php

namespace Adm\Http\Controllers;

use Adm\Config\AdminUserConfig;
use Adm\Config\AdminUserGroupConfig;
use Adm\Config\LanguagesConfig;
use Adm\Config\RedirectConfig;
use App\Models\Language;
use App\Models\Redirect;
use Illuminate\Validation\ValidationException;

class CoreController extends AdmController
{
    public function admin_users($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            AdminUserConfig::class
        );
    }

    public function admin_user_groups($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            AdminUserGroupConfig::class
        );
    }

    public function redirects($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            RedirectConfig::class
        );
    }

    public function import_redirects($method, $id)
    {
        if (request()->isMethod('POST')) {
            $this->validate(
                request(),
                [
                    'redirect_csv'  => ['required', 'file', 'mimes:csv']
                ],
                [
                    'redirect_csv.required' => 'Please select a CSV',
                    'redirect_csv.file'     => 'Please select a file',
                    'redirect_csv.mimes'    => 'Please ensure the file is a CSV'
                ]
            );

            $domain = request()->input('old_domain', '');

            $File = request()->file('redirect_csv');
            $fp = fopen($File->path(), 'r');
            $headers_read = false;
            $row_count = 0;
            $redirects = [];
            while ($row = fgetcsv($fp)) {
                $row_count++;
                if (!$headers_read) {
                    $headers_read = true;
                    continue;
                }

                $old_url = isset($row[0]) ? trim($row[0]) : false;
                $new_url = isset($row[1]) ? trim($row[1]) : false;
                $code = isset($row[2]) && trim($row[2]) == 302 ? 302 : 301;

                if (isset($row[3]) && !empty($row[3])) {
                    throw ValidationException::withMessages([
                        'redirect_csv'  => 'The CSV contains too many columns. Please make sure it only contains an old URL, a new URL & an optional redirect code (301 or 302)'
                    ]);
                }

                if (empty($old_url)) {
                    throw ValidationException::withMessages([
                        'redirect_csv'  => 'CSV row ' . $row_count . ' is missing an old URL. If you want to redirect the home page, please include a slash.'
                    ]);
                }
                if (empty($new_url)) {
                    throw ValidationException::withMessages([
                        'redirect_csv'  => 'CSV row ' . $row_count . ' is missing an new URL. If you want to redirect the home page, please include a slash.'
                    ]);
                }

                $old_url = ltrim(str_replace($domain, '', $old_url), '/');
                $new_url = ltrim(str_replace($domain, '', $new_url), '/');
                if (empty($old_url)) { $old_url = '/'; }
                if (empty($new_url)) { $new_url = '/'; }

                $redirects[] = [
                    'old_url'       => $old_url,
                    'new_url'       => $new_url,
                    'redirect_type' => $code,
                    'status'        => true
                ];
            }
            fclose($fp);

            foreach ($redirects as $redirect) {
                Redirect::create($redirect);
            }

            $this->Crud->flushCache();

            return response()
                ->redirectTo(route('adm.route', 'redirects'))
                ->with('success', sizeof($redirects) . ' imported successfully');
        }

        return view(
            'redirects.import',
            [
                'title'         => 'Redirect importer',
                'import_url'    => route('adm.route', 'import-redirects'),
            ]
        );
    }

    public function languages($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            LanguagesConfig::class
        );
    }

    public function change_language()
    {
        $lang = request()->input('language');
        $slug = request()->input('slug');
        $Lang = Language::find($lang);
        if (!$Lang) {
            return response()->redirectTo(route('adm.route', $slug))
                ->with('error', 'Sorry, that language could not be found.');
        }

        $this->Crud->setLanguageId($lang);
        return response()->redirectTo(route('adm.route', $slug))
            ->with('success', 'Language changed to ' . $Lang->title);
    }
}