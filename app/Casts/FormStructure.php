<?php

namespace App\Casts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Forms\Models\FormField;

class FormStructure extends SharedAppAdmCast
{
    /**
     * {@inheritdoc}
     * 
     * @return Collection
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        if (empty($value) || !Str::isJson($value)) {
            return collect([]);
        }

        $structure = collect(json_decode($value, true));
        $this->loadFormStructure($structure);

        return $structure;
    }

    /**
     * {@inheritdoc}
     * 
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof Collection) { return $value; }

        if (empty($value) || !is_array($value) || !isset($value['rows'])) {
            $this->deleteFormFields($model->id);
        }

        $field_ids_to_keep = [];
        $structure = [
            'rows'  => []
        ];
        foreach ($value['rows'] as $row_idx => $row) {
            $structure['rows'][$row_idx] = [
                'columns'   => []
            ];

            if (!isset($row['columns'])) { continue; }

            foreach ($row['columns'] as $col_idx => $col) {
                $structure['rows'][$row_idx]['columns'][$col_idx] = [
                    'fields'    => []
                ];

                if (!isset($col['fields'])) { continue; }

                foreach ($col['fields'] as $field_idx => $field) {
                    $FormField = $this->addGetFormField($field, $model->id);
                    $structure['rows'][$row_idx]['columns'][$col_idx]['fields'][$field_idx] = $FormField->id;
                    $field_ids_to_keep[] = $FormField->id;
                }
            }
        }

        FormField::where('form_id', $model->id)
            ->whereNotIn('id', $field_ids_to_keep)
            ->delete();

        return json_encode($structure);
    }

    /**
     * Load the form's field structure, based on it's stored
     * rows & columns - storing the field back into the structure
     * 
     * @param Collection $structure
     * @return void
     */
    protected function loadFormStructure(Collection $structure): void
    {
        $rows = $structure['rows'];
        foreach ($rows as $row_idx => $row) {
            foreach ($row['columns'] as $col_idx => $column) {
                foreach ($column['fields'] as $field_idx => $field_id) {
                    $FormField = FormField::find($field_id);
                    if ($FormField) {
                        data_set($FormField, 'form_field_id', Str::slug($FormField->label, '-') . '-' . uniqid());
                    }

                    data_set($rows, $row_idx . '.columns.' . $col_idx . '.fields.' . $field_idx, $FormField);
                }
            }
        }
        data_set($structure, 'rows', $rows);
    }

    /**
     * Delete a form's fields, based on it's form ID
     * 
     * @param int $form_id
     * @return void
     */
    protected function deleteFormFields(int $form_id): void
    {
        FormField::where('form_id', $form_id)->delete();
    }

    /**
     * Add, or retrieve, a form field based on it's details
     * & form ID
     * 
     * @param array $field
     * @param int $form_id
     * @return FormField
     */
    protected function addGetFormField(array $field, int $form_id): FormField
    {
        $FormField = null;
        if (isset($field['id'])) {
            $FormField = FormField::where('id', $field['id'])
            ->where('form_id', $form_id)->first();
        }

        if(!$FormField) { $FormField = new FormField(); }

        $FormField->form_id = $form_id;
        $FormField->label = isset($field['label']) ? $field['label'] : null;
        $FormField->help_text = isset($field['help_text']) ? $field['help_text'] : null;
        $FormField->type = isset($field['type']) ? $field['type'] : null;
        $FormField->options = isset($field['options']) ? $field['options'] : null;
        $FormField->settings = isset($field['settings']) ? $field['settings'] : null;
        $FormField->required = isset($field['required']) ? $field['required'] : null;
        $FormField->code_reference = isset($field['code_reference']) ? $field['code_reference'] : null;
        $FormField->save();

        return $FormField;
    }
}