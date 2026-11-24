<?php

namespace Adm\Http\Controllers;

use Adm\Config\MenuConfig;
use App\Models\Menu;
use App\Models\Scopes\LanguageScope;
use App\Models\Url;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ReflectionClass;

class MenuController extends AdmController
{
    /**
     * Handle CRUD functionality for menus
     * 
     * @param ?string $method
     * @param ?int $id
     * @return mixed
     */
    public function menus(?string $method, ?int $id): mixed
    {
        switch ($method) {
            case 'edit':
                return $this->menuEdit($id);
            case 'update':
                return $this->menuUpdate($id);
            case 'translate':
                return $this->menuTranslate($id);
            default: 
                return $this->Crud->boot(
                    $method,
                    $id,
                    MenuConfig::class
                );
        }
    }

    /**
     * Loads the menu drag/drop edit interface
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    protected function menuEdit(int $id): View
    {
        $Menu = Menu::where('menu_id', $id)->first();
        
        return view(
            'menu.edit',
            [
                'Menu'          => $Menu,
                'link_types'    => $this->getLinkTypes(),
                'form_action'   => $this->Crud->getUrlRoute('menus', 'update', $id),
                'breadcrumbs'   => [
                    ['url' => route('adm.route'),           'title' => 'Dashboard'],
                    ['url' => route('adm.route', 'menus'),  'title' => 'Menus'],
                    ['url' => '#',                          'title' => 'Edit ' . $Menu->title]
                ],
                'super_admin'   => Auth::guard('adm')->user()->is_super_admin,
                'config_fields' => MenuConfig::$fields
            ]
        );
    }

    /**
     * Updates a menu
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function menuUpdate(int $id): RedirectResponse
    {
        $Menu = Menu::where('menu_id', $id)->first();

        $Menu->structure = request()->input('links');
        $Menu->save();

        $this->Crud->flushCache();

        return response()->redirectTo($this->Crud->getUrlRoute('menus', 'edit', $id))
        ->with('success', 'Menu updated successfully.');
    }

    /**
     * 'Translates' a menu by copying & storing an existing
     * menu with a new language_id
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function menuTranslate(int $id): RedirectResponse
    {
        $TranslatedExists = Menu::withoutGlobalScope(LanguageScope::class)
            ->where([
                'menu_id'       => $id,
                'language_id'   => request()->query('lang-to')
            ])
            ->first();
            
        if( !$TranslatedExists) {
            $Menu = Menu::where('menu_id', $id)->first();

            $Translated = Menu::create([
                'menu_id'           => $id,
                'language_id'       => request()->query('lang-to'),
                'title'             => $Menu->title,
                'code_reference'    => $Menu->code_reference,
                'structure'         => [],
                'nest_limit'        => $Menu->nest_limit
            ]);

            $this->Crud->flushCache();
        }

        $this->Crud->setLanguageId(request()->query('lang-to'));
        return response()->redirectTo($this->Crud->getUrlRoute('menus', 'edit', $id));
    }

    /**
     * Returns an array of link types available to be
     * added to menus
     * 
     * @return array
     */
    protected function getLinkTypes(): array
    {
        $types = [];

        $unique_classes = Url::select('urlable_type')->distinct()->get()->pluck('urlable_type')->toArray();
        foreach ($unique_classes as $class) {
            $RefClass = new ReflectionClass($class);
            $title = Str::plural(ucfirst(str_replace('_', ' ', strtolower(Str::snake($RefClass->getShortName())))));
            if ($RefClass->hasProperty('config_class')) {
                $ClassInst = $RefClass->newInstance();
                $ConfClass = new ReflectionClass($ClassInst->config_class);
                $title = $ConfClass->getStaticPropertyValue('title');
            }

            $types[$class] = $title;
        }

        return $types;
    }

    /**
     * Returns a JSON response of all available links
     * within the POSTed types
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLinks(): JsonResponse
    {
        $links_by_type = [];
        $type_title_fields = [];

        $Urls = Url::whereIn('urlable_type', request()->input('types'))->get();
        foreach ($Urls as $Url) {
            if (!$Url->urlable) { continue; } // If the URL has no linked resource, don't show it

            $type_class = $Url->urlable_type;

            if (!isset($links_by_type[$type_class])) {
                $links_by_type[$type_class] = [];
            }
            if (!isset($type_title_fields[$type_class])) {
                $type_title_fields[$type_class] = $this->getUrlableTitleFieldForClass($type_class);
            }

            $links_by_type[$type_class][] = [
                'url_id'    => $Url->id,
                'title'     => $Url->urlable->{$type_title_fields[$type_class]}
            ];
        }

        foreach ($links_by_type as $class => $links) {
            $links = collect($links);
            $links = $links->sortBy('title');

            $links_by_type[$class] = $links;
        }

        return response()->json(['links' => $links_by_type]);
    }

    /**
     * Returns a partial view of a link added to the
     * menu structure
     * 
     * @return \Illuminate\Http\Response
     */
    public function linkAdded(): Response
    {
        $Menu = Menu::where('menu_id', request()->input('menu_id'))->first();

        $type = request()->input('type');
        $value = request()->input('value');
        $title = request()->input('title');
        $depth = request()->input('depth');
        $prefix = request()->input('prefix');

        $link = '';
        $new_tab = false;
        switch ($type) {
            case 'internal':
                $Url = Url::find($value);
                if ($Url && $Url->urlable) {
                    $title_field = $this->getUrlableTitleFieldForClass($Url->urlable_type);
                    $title = empty($title) ? $Url->urlable->{$title_field} : $title;
                    $link = route('theme', $Url->url);
                }
                break;
            case 'external':
                $link = $value;
                $new_tab = true;
                break;
        }

        return response()->view(
            'menu.partials.structure-item',
            [
                'unique_id'     => uniqid(),
                'type'          => $type,
                'value'         => $value,
                'depth'         => $depth,
                'prefix'        => $prefix,
                'title'         => $title,
                'link'          => $link,
                'new_tab'       => $new_tab,
                'nofollow'      => false,
                'include_inner' => $depth < $Menu->nest_limit
            ]
        );
    }

    /**
     * Updates a menu's details
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_menu_details(): JsonResponse
    {
        $Menu = Menu::where('menu_id', request()->input('menu'))->first();
        $Menu->title = request()->input('title');
        $Menu->code_reference = request()->input('code_reference');
        $Menu->nest_limit = (int)request()->input('nest_limit');
        $Menu->save();

        $this->Crud->flushCache();

        return response()->json([
            'success'   => true
        ]);
    }

    /**
     * Returns the title field to display for a link
     * based on it's class
     * 
     * @param string $type_class
     * @return string
     */
    protected function getUrlableTitleFieldForClass(string $type_class): string
    {
        $ClassInst = new $type_class();
        return property_exists($ClassInst, 'url_search_field') ? $ClassInst->url_search_field : 'title';
    }
}