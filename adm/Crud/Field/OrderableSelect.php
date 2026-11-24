<?php

namespace Adm\Crud\Field;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionClass;

class OrderableSelect extends Field
{
    /**
     * {@inheritdoc}
     */
    public function prepareForListing(Model $Item, string $field, array $details): void
    {
        $selected = isset($details['relation']) && !isset($details['options']) ? $this->getStoredRelatedOptionsForItem($Item, $field, $details) : data_get($Item, $field);
        $opts = $this->checkGetFieldOptions($details);
        $selected_labels = [];
        foreach ($selected as $selected_value) {
            if (isset($opts[$selected_value])) {
                $selected_labels[] = $opts[$selected_value];
            }
        }
        data_set($Item, $field, $selected_labels);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForForm(Model|array $Item, string $field, array $details): void
    {
        if ($Item && isset($details['relation']) && !isset($details['options'])) {
            data_set($Item, $field, $this->getStoredRelatedOptionsForItem($Item, $field, $details));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForStorage(Model|array $Item, string $field, mixed $value, array $details): void
    {
        if (isset($details['relation']) && isset($details['relation']['storage_table'])) {
            return;
        }
        
        if (isset($details['options']) || isset($details['generate_options'])) {
            data_set($Item, $field, $value);
        }
        if (isset($details['relation']) && !isset($details['relation']['storage_table'])) {
            data_set($Item, $field, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function modifyFieldDetailsForForm(array $details, ?Model $Item): array
    {
        $opts = $this->checkGetFieldOptions($details);
        $details['options'] = $opts;
        return $details;
    }

    /**
     * {@inheritdoc}
     */
    public function afterStored(Model $Item, string $field, mixed $value, array $details): void
    {
        if ($Item->version == -1) { return; }
        
        if (isset($details['relation']) && isset($details['relation']['storage_table']) && !isset($details['options'])) {
            $class_id = $this->getClassIdForStorage($details);
            $pivot_class_id = $this->getClassPivotIdForStorage($details);
            $related_class_id = $this->getRelatedClassIdForStorage($details);

            // Firstly, clear out existing data
            DB::table($details['relation']['storage_table'])
                ->where($pivot_class_id, data_get($Item, $class_id))
                ->delete();

            $insert_rows = [];
            foreach ((array)$value as $sort_order => $related_value) {
                $this_row = [
                    $pivot_class_id     => data_get($Item, $class_id),
                    $related_class_id   => $related_value
                ];

                if (isset($details['relation']['sort_field'])) {
                    $this_row[$details['relation']['sort_field']] = $sort_order;
                }

                $insert_rows[] = $this_row;
            }

            DB::table($details['relation']['storage_table'])->insert($insert_rows);
        }
    }

    /**
     * Checks the field details for configuration about the
     * available options
     * 
     * @param array $field_details
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function checkGetFieldOptions(array $field_details): array
    {
        if (!isset($field_details['options']) && !isset($field_details['relation']) && !isset($field_details['generate_options'])) {
            throw new InvalidArgumentException('No options, relation or generate_options function found for field \'' . $field_details['label'] . '\'.');
        }

        if (isset($field_details['options'])) {
            return $field_details['options'];
        }

        if (isset($field_details['generate_options'])) {
            return $this->getGeneratedOptions($field_details);
        }

        return $this->getRelatedOptions($field_details);
    }

    /**
     * If the field is using a generated set of options,
     * return the generated set ready for use in the CMS
     * 
     * @param array $field_details
     * @return mixed
     */
    protected function getGeneratedOptions(array $field_details): mixed
    {
        $gen_method = $field_details['generate_options'];

        if (is_array($gen_method)) {
            $Target = new ReflectionClass($gen_method[0]);
            if (!$Target->hasMethod($gen_method[1])) {
                throw new InvalidArgumentException($gen_method[0] . ' class doesn\'t have available method \'' . $gen_method[1] . '\'.');
            }

            return $Target->getMethod($gen_method[1])->invoke(null);
        }

        $Conf = $this->CrudInterface->getConfigClass();
        if (!$Conf->hasMethod($gen_method)) {
            throw new InvalidArgumentException('Config class doesn\'t have available method \'' . $gen_method . '\'.');
        }

        return $Conf->getMethod($gen_method)->invoke(null);
    }

    /**
     * If the field is using a relational set of options,
     * return the generated set of relations ready for use
     * 
     * @param array $field_details
     * @param ?\Illuminate\Database\Eloquent\Model $Item
     * @param ?string $search_term
     * @param array $existing_match_list
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function getRelatedOptions(
        array $field_details, 
        ?Model $Item = null, 
        ?string $search_term = null, 
        array $existing_match_list = []
    ): array
    {
        if (!isset($field_details['relation'])) {
            throw new InvalidArgumentException('No relation config found for field \'' . $field_details['label'] . '\'.');
        }
        if (!isset($field_details['relation']['to'])) {
            throw new InvalidArgumentException('No relation \'to\' config found for field \'' . $field_details['label'] . '\'.');
        }

        $related_class = $field_details['relation']['to'];
        if (method_exists($related_class, 'getAdmSelectOptions')) {
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

        $display_title = $model_title;
        if (isset($field_details['relation']['display_title'])) {
            $display_title = $field_details['relation']['display_title'];
        }

        $Qry = $related_class::query()->withoutGlobalScopes();
        if ($related_class::isSoftDeletable()) {
            $Qry->whereNull('deleted_at');
        }
        $this->CrudInterface->applyLanguageCondition($Qry, $related_class);
        $Qry->select($model_id, $model_title);

        if (isset($field_details['relation']['filter_callback']) && !empty($field_details['relation']['filter_callback'])) {
            $ConfigClass = $this->CrudInterface->getConfigClass();
            if ($ConfigClass->hasMethod($field_details['relation']['filter_callback'])) {
                $ConfigClass->getMethod($field_details['relation']['filter_callback'])->invoke(null, $Qry, $Item);
            }
        }

        if ($search_term || !empty($existing_match_list)) {
            $Qry->where(function ($Subquery) use ($search_term, $existing_match_list, $model_id, $model_title) {
                if (!empty($existing_match_list)) {
                    $Subquery->whereIn($model_id, $existing_match_list);
                }
                if (!empty($search_term)) {
                    if (is_array($model_title)) {
                        foreach ($model_title as $title_field) {
                            $Subquery->orWhere($title_field, 'LIKE',  '%' . $search_term . '%');
                        }
                    } else {
                        $Subquery->orWhere($model_title, 'LIKE', '%' . $search_term . '%');
                    }
                }
            });
        }

        if (isset($field_details['relation']['order_by_field'])) {
            $sort = isset($field_details['relation']['order_by_sort']) ? $field_details['relation']['order_by_sort'] : 'asc';
            $Qry->orderBy($field_details['relation']['order_by_field'], $sort);
        }

        $Related = $Qry->get();

        $opts = [];
        if ($Related->isNotEmpty()) {
            foreach ($Related as $LinkedItem) {
                $opts[$LinkedItem->{$model_id}] = $LinkedItem->{$display_title};
            }
        }

        return $opts;
    }

    /**
     * Returns an array of key => value pairs representing
     * the stored information for the item
     * 
     * @param \Illuminate\Database\Eloquent\Model $Item
     * @param string $field
     * @param array $field_details
     * @return array
     */
    protected function getStoredRelatedOptionsForItem(Model $Item, string $field, array $field_details): array
    {
        if (isset($field_details['relation']['storage_table'])) {
            $class_id = $this->getClassIdForStorage($field_details);
            $pivot_class_id = $this->getClassPivotIdForStorage($field_details);
            $related_class_id = $this->getRelatedClassIdForStorage($field_details);

            $SelectedOptsQry = DB::table($field_details['relation']['storage_table'])
                ->where($pivot_class_id, data_get($Item, $class_id));

            if (isset($field_details['relation']['sort_field'])) {
                $SelectedOptsQry->orderBy($field_details['relation']['sort_field']);
            }

            return $SelectedOptsQry->get()
                ->pluck($related_class_id)
                ->toArray();
        } else {
            $related_class_id = $this->getRelatedClassIdForStorage($field_details);

            $StoredOptions = data_get($Item, $field);
            if ($StoredOptions instanceof Collection) {
                return $StoredOptions->pluck($related_class_id)->toArray();
            }

            return $StoredOptions;
        }
    }

    /**
     * Returns the 'primary' ID field used to store the current
     * item's details for the field
     * 
     * @param array $field_details
     * @return string
     */
    protected function getClassIdForStorage(array $field_details): string
    {
        return isset($field_details['relation']['class_id']) 
            ? $field_details['relation']['class_id'] 
            : $this->CrudInterface->getClassIDField();
    }

    /**
     * Returns the 'primary' pivot ID field used to store the current
     * item's details in the storage table for the field
     * 
     * @param array $field_details
     * @return string
     */
    protected function getClassPivotIdForStorage(array $field_details): string
    {
        return isset($field_details['relation']['class_pivot_id'])
            ? $field_details['relation']['class_pivot_id']
            : $this->CrudInterface->getClassIDField();
    }

    /**
     * Returns the 'primary' ID field used to store the related
     * item's details for the field
     * 
     * @param array $field_details
     * @return string
     */
    protected function getRelatedClassIdForStorage(array $field_details): string
    {
        return isset($field_details['relation']['related_class_id']) 
            ? $field_details['relation']['related_class_id'] 
            : $this->CrudInterface->getPrimaryIDForClass($field_details['relation']['to']);
    }

    /**
     * {@inheritdoc}
     */
    public function search(Builder $query, string $table, string $field, string $search_term, array $field_details): void
    {
        // TODO
    }

    /**
     * {@inheritdoc}
     */
    public function sort(Builder $query, string $table, string $field_name, string $sort_dir, array $field_details): void
    {
        // TODO
    }

    /**
     * Returns a JSON response containing options to
     * include in the relational field
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRelatedOptions(): JsonResponse
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

    /**
     * Returns a JSON response containing options to
     * include in the relational field when filtered
     * by an AJAX call
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchRelatedOptions(): JsonResponse
    {
        $class_name = request()->input('relation_class');
        $search = request()->input('search');
        $existing = request()->input('selected');

        if (empty($class_name)) {
            return response()->json([]);
        }

        if (!empty($existing) && Str::isJson($existing)) {
            $existing = json_decode($existing, true);
        }

        if (empty($search) && empty($existing)) {
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

        $related_opts = [];
        foreach ($this->getRelatedOptions($field_details, null, $search, $existing) as $id => $label) {
            $related_opts[$id] = [
                'id'    => $id,
                'label' => $label
            ];
        }
        if (!empty($existing)) {
            $ordered_opts = [];
            $used_ids = [];
            foreach ($existing as $id) {
                $ordered_opts[] = $related_opts[$id];
                $used_ids[] = $id;
            }
            foreach ($related_opts as $id => $opt) {
                if (!in_array($id, $used_ids)) {
                    $ordered_opts[] = $opt;
                }
            }
            $related_opts = $ordered_opts;
        }

        return response()->json([
            'success'   => true,
            'init'      => request()->input('init') == "true",
            'opts'      => $related_opts
        ]);
    }
}