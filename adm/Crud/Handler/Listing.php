<?php

namespace Adm\Crud\Handler;

use Adm\Crud\CrudField;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

/**
 * Request handler used to process CMS listings
 */
class Listing extends RequestHandler
{
    /**
     * Default pagination limit to use for listings
     * 
     * @var int
     */
    const DEFAULT_PAGINATION_LIMIT  = 25;

    /**
     * The columns to display in listings
     * 
     * @var array
     */
    protected $columns  = [];

    /**
     * Handle the listings for the current resource
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function handle(): View
    {
        $this->validateSetColumns();

        return view(
            'crud.list',
            [
                'title'         => $this->getConfigProperty('title'),
                'title_single'  => $this->getSingularConfigProperty('title'),
                'description'   => $this->getConfigProperty('description'),
                'breadcrumbs'   => $this->generateBreadcrumbs('', null),
                'item_id'       => $this->getClassIDField(),
                'columns'       => $this->columns,
                'Items'         => $this->getItems(),
                'add_button'    => [
                    'enabled'       => $this->getConfigProperty('add_button_enabled', true),
                    'text'          => $this->getConfigProperty('add_button_text', 'Create ' . Str::lower(Str::singular(($this->getConfigProperty('title'))))),
                    'url'           => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'create', null),
                ],
                'edit_button'   => [
                    'enabled'           => $this->getConfigProperty('edit_button_enabled', true),
                    'text'              => $this->getConfigProperty('edit_button_text', 'Edit'),
                    'url_pattern'       => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'edit', '_ID_'),
                    'id_placeholder'    => '_ID_',
                ],
                'copy_button'   => [
                    'enabled'           => $this->getConfigProperty('copy_button_enabled', true),
                    'text'              => $this->getConfigProperty('copy_button_text', 'Copy'),
                    'url_pattern'       => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'copy', '_ID_'),
                    'id_placeholder'    => '_ID_',
                ],
                'read_button'   => [
                    'enabled'           => $this->getConfigProperty('read_button_enabled', false),
                    'text'              => $this->getConfigProperty('read_button_text', 'View'),
                    'url_pattern'       => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'read', '_ID_'),
                    'id_placeholder'    => '_ID_',
                ],
                'translate_button'  => [
                    'enabled'           => $this->getConfigProperty('translate_button_enabled', $this->CrudInterface->isClassTranslatable()),
                    'text'              => $this->getConfigProperty('translate_button_text', 'Translate to'),
                    'url_pattern'       => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'translate', '_ID_') . '?lang-from=_LANG_&lang-to=_TO_',
                    'id_placeholder'    => '_ID_',
                    'lang_placeholder'  => '_LANG_',
                    'to_placeholder'    => '_TO_',
                    'languages'         => $this->CrudInterface->getLanguages()
                ],
                'export_button' => [
                    'enabled'           => $this->getConfigProperty('export_button_enabled', false),
                    'text'              => $this->getConfigProperty('export_button_text', 'Export'),
                    'url'               => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'export', null) . $this->getCommonUrlParameters()
                ],
                'delete_action' => [
                    'enabled'           => !$this->getConfigProperty('delete_disabled', false),
                    'url'               => $this->getCrudUrl($this->getConfigProperty('url_slug'), 'delete', null) . $this->getCommonUrlParameters(true)
                ],
                'extra_actions' => $this->getConfigProperty('extra_actions', []),

                'hide_search'           => $this->getConfigProperty('hide_search', false),
                'search_term'           => request()->query('search'),
                'search_action'         => $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null),
                'search_placeholder'    => $this->getConfigProperty('search_placeholder', 'Search ' . strtolower($this->getConfigProperty('title'))),

                'sort_field'    => $this->CrudInterface->getItemSortField(),
                'sort_dir'      => $this->CrudInterface->getItemSortDirection(),

                'broadcast_channel'     => $this->getClassBroadcastChannel()
            ]
        );
    }

    /**
     * Validate the provided column set, or load the default column 
     * configuration
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateSetColumns(): void
    {
        if (!$this->Config->hasProperty('listing_columns')) {
            $this->setDefaultColumns();
            return;
        }

        $fields = $this->getConfigProperty('fields');
        $cols = $this->getConfigProperty('listing_columns');
        $casts = $this->CrudInterface->getClassCasts();
        foreach ($cols as $col) {
            if (!isset($fields[$col])) {
                throw new InvalidArgumentException('Fieldset missing column ' . $col);
            }

            $Field = new CrudField($this->CrudInterface, $fields[$col]['type']);
            $fields[$col] = $Field->modifyFieldDetailsForListing($fields[$col]);

            if (empty($casts) || (!isset($casts[$col]) || $casts[$col] != 'encrypted')) {
                $fields[$col] = $Field->setSortFields($col, $fields[$col], $this->getCrudUrl($this->getConfigProperty('url_slug'), '', null));
            }

            $this->columns[$col] = $fields[$col];
        }
    }

    /**
     * Attempt to set default column configuration (title or name)
     * If attributes don't exist, throw exception
     * 
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function setDefaultColumns(): void
    {
        $item_class = $this->getConfigProperty('class');
        $Item = new $item_class();

        // First, check for title
        if (array_key_exists('title', $Item->getAttributes())) {
            $this->columns['title'] = [
                'label'         => 'Title',
                'help'          => '',
                'type'          => 'string'
            ];
        }
        // Else, check for name
        elseif (array_key_exists('name', $Item->getAttributes())) {
            $this->columns['name'] = [
                'label'         => 'Name',
                'help'          => '',
                'type'          => 'string'
            ];
        }
        // Else, throw exception
        else {
            throw new InvalidArgumentException('Default listing columns could not be generated as title or name field does not exist.');
        }
    }

    /**
     * Retrieve the items to display in listings
     * 
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function getItems(): LengthAwarePaginator
    {
        if (request()->query('search') && $this->configClassHasMethod('customListingSearch')) {
            $Items = $this->Config->getMethod('customListingSearch')->invoke(
                null,
                request()->query('search'),
                $this->CrudInterface->getLanguageId(),
                self::DEFAULT_PAGINATION_LIMIT,
                $this->CrudInterface->getItemSortField(),
                $this->CrudInterface->getItemSortDirection(),
                false
            );
            return $this->processItems($Items);
        }
        
        $this_table = $this->getClassTableName();

        $item_class = $this->getConfigProperty('class');
        $Qry = $item_class::query()->withoutGlobalScopes();

        if ($this->classHasSoftDeletes()) {
            $Qry->whereNull($this_table . '.deleted_at');
        }
        $this->CrudInterface->applyLanguageCondition($Qry);

        $this->filterItemsQuery($Qry, $item_class, $this->columns);
        $this->sortItemsQuery($Qry, $this->columns);

        if ($this->configClassHasMethod('alterListingItemsQuery')) {
            $this->Config->getMethod('alterListingItemsQuery')->invoke(null, $Qry);
        }

        $pagi_limit = $this->Config->hasProperty('pagination_limit') ?
            $this->getConfigProperty('pagination_limit') : 
            self::DEFAULT_PAGINATION_LIMIT;

        $Items = $Qry->paginate($pagi_limit);
        $Items->appends(request()->except('page'));

        return $this->processItems($Items);
    }

    /**
     * Processes each item & fields to prepare them for display
     * in listings
     * 
     * @param \Illuminate\Pagination\LengthAwarePaginator $Items
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    protected function processItems(LengthAwarePaginator $Items): LengthAwarePaginator
    {
        $cols = $this->columns;
        $Items->each(function ($Item, $key) use ($cols) {
            foreach ($cols as $field_name => $details) {
                $Field = new CrudField($this->CrudInterface, $details['type']);
                $Field->prepareForListing($Item, $field_name, $details);

                $this->columns[$field_name] = $Field->modifyFieldDetailsForListing($details);
            }
        });
        return $Items;
    }
}