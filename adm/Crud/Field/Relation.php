<?php

namespace Adm\Crud\Field;

use App\Helpers\ClassFinder;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Relation extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $selected = data_get($Item, $field);
        $opts = $this->getRelatedOptions($details);
        if (!empty($opts) && isset($opts[$selected])) {
            data_set($Item, $field, $opts[$selected]);
        } else {
            data_set($Item, $field, '');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        $opts = $this->getRelatedOptions($details, $Item);
        $details['relation']['options'] = $opts;

        try {
            $RelatedConfig = ClassFinder::findConfigClassForModel($details['relation']['to']);
            if ($RelatedConfig) {
                /** @var \Adm\Models\AdmUser */
                $User = Auth::guard('adm')->user();
                $url = $RelatedConfig->getStaticPropertyValue('url_slug');
                if ($User->canAccess($url)) {
                    $details['relation']['create_link'] = [
                        'url'   => route('adm.route', [$url, 'create']),
                        'text'  => 'Create new ' . Str::singular(strtolower($RelatedConfig->getStaticPropertyValue('title')))
                    ];
                }
            }
        } catch (Exception $e) {}

        return $details;
    }

    /**
     * Returns relational options to display in CMS forms
     * 
     * @param array $field_details
     * @param ?Model $Item
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getRelatedOptions(array $field_details, ?Model $Item = null): array
    {
        if (!isset($field_details['relation'])) {
            throw new InvalidArgumentException('No relation config found for field \'' . $field_details['label'] . '\'.');
        }
        if (!isset($field_details['relation']['to'])) {
            throw new InvalidArgumentException('No relation \'to\' config found for field \'' . $field_details['label'] . '\'.');
        }

        $related_class = $field_details['relation']['to'];
        if (method_exists($related_class, 'getAdmSelectOptions')) {
            $this->sortable = false;
            return $related_class::getAdmSelectOptions();
        }

        $model_id = $this->CrudInterface->getPrimaryIDForClass($related_class);
        if (isset($field_details['relation']['id_field'])) {
            $model_id = $field_details['relation']['id_field'];
        }

        $model_title = 'title';
        if (isset($field_details['relation']['title_field'])) {
            $model_title = $field_details['relation']['title_field'];
        }

        $Qry = $related_class::query()->withoutGlobalScopes();
        if ($related_class::isSoftDeletable()) {
            $Qry->whereNull('deleted_at');
        }

        if (isset($field_details['relation']['exclude_current']) && $field_details['relation']['exclude_current']) {
            $config_class = $this->CrudInterface->getConfigClassName();
            if ($config_class == $related_class && $Item) {
                $Qry->whereNot($model_id, $Item->$model_id);
            }
        }

        if (isset($field_details['relation']['filter_callback']) && !empty($field_details['relation']['filter_callback'])) {
            $ConfigClass = $this->CrudInterface->getConfigClass();
            if ($ConfigClass->hasMethod($field_details['relation']['filter_callback'])) {
                $ConfigClass->getMethod($field_details['relation']['filter_callback'])->invoke(null, $Qry);
            }
        }

        $this->CrudInterface->applyLanguageCondition($Qry, $related_class);
        $Qry->select($model_id, $model_title);

        if (isset($field_details['relation']['order_by_field'])) {
            $sort = isset($field_details['relation']['order_by_sort']) ? $field_details['relation']['order_by_sort'] : 'asc';
            $Qry->orderBy($field_details['relation']['order_by_field'], $sort);
        } else {
            $Qry->orderBy($model_title, 'asc');
        }

        $Related = $Qry->get();

        $empty_label = 'Select a ' . strtolower($field_details['label']);
        $empty_value = 0;
        if (isset($field_details['relation']['empty_value'])) {
            $empty_value = $field_details['relation']['empty_value'];
        }
        if (isset($field_details['relation']['empty_label'])) {
            $empty_label = $field_details['relation']['empty_label'];
        }

        $opts = [
            $empty_value => $empty_label
        ];
        if ($Related->isNotEmpty()) {
            foreach ($Related as $LinkedItem) {
                $opts[$LinkedItem->{$model_id}] = $LinkedItem->{$model_title};
            }
        }

        return $opts;
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        $related_class = $field_details['relation']['to'];
        if (method_exists($related_class, 'getAdmSelectOptions')) {
            $related_options = $related_class::getAdmSelectOptions();
            $searched_values = array_filter($related_options, function ($entry) use ($search_term) {
                return stripos($entry, $search_term) !== false;
            });

            $query->orWhereIn($table . '.' . $field, array_keys($searched_values));
            return;
        }

        $model_id = $this->CrudInterface->getPrimaryIDForClass($related_class);
        if (isset($field_details['relation']['id_field'])) {
            $model_id = $field_details['relation']['id_field'];
        }
        $model_title = 'title';
        if (isset($field_details['relation']['title_field'])) {
            $model_title = $field_details['relation']['title_field'];
        }
        $Qry = $related_class::query()->withoutGlobalScopes();
        if ($related_class::isSoftDeletable()) {
            $Qry->whereNull('deleted_at');
        }
        $this->CrudInterface->applyLanguageCondition($Qry, $related_class);
        $matched_ids = $Qry->select($model_id)
            ->where($model_title, 'LIKE', '%' . $search_term . '%')
            ->get()->pluck($model_id)->toArray();

        $query->orWhereIn($table . '.' . $field, $matched_ids);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(Builder $query, string $table, string $field_name, string $sort_dir, array $field_details): void
    {
        $related_class = $field_details['relation']['to'];
        if (
            method_exists($related_class, 'getAdmSelectOptions') &&
            (!isset($field_details['relation']['db_sort']) || !$field_details['relation']['db_sort'])
        ) {
            // If values aren't DB driven, order by saved value
            $query->orderBy($table . '.' . $field_name, $sort_dir);
            return;
        }

        $Related = new $related_class();
        $model_id = $this->CrudInterface->getPrimaryIDForClass($related_class);
        if (isset($field_details['relation']['id_field'])) {
            $model_id = $field_details['relation']['id_field'];
        }
        $model_title = 'title';
        if (isset($field_details['relation']['title_field'])) {
            $model_title = $field_details['relation']['title_field'];
        }

        $related_table = $Related->getTable();
        $query->select($table . '.*')
            ->join($related_table, $related_table . '.' . $model_id, '=', $table . '.' . $field_name)
            ->orderBy($related_table . '.' . $model_title, $sort_dir);
    }

    /**
     * Returns a JSON response containing related options
     * to display in the field
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDropdownOptions(): JsonResponse
    {
        $class_name = request()->input('relation_class');
        $config = request()->input('relation_config');
        
        if (empty($class_name)) {
            return response()->json([]);
        }

        $field_details = [
            'label'     => '',
            'relation'  => [
                'to'        => $class_name
            ]
        ];
        if (!empty($config) && Str::isJson($config)) {
            $config = json_decode($config, true);
            if (isset($config['id'])) {
                $field_details['relation']['id_field'] = $config['id'];
            }
            if (isset($config['title'])) {
                $field_details['relation']['title_field'] = $config['title'];
            }
        }

        return response()->json([
            'success'   => true,
            'opts'      => $this->getRelatedOptions($field_details)
        ]);
    }
}