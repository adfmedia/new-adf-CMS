<?php

namespace Adm\Http\Controllers;

use Adm\Config\CommonTextConfig;
use App\Models\CommonText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use InvalidArgumentException;

class CommonTextController extends AdmController
{
    /**
     * Shows the 'main' common text core field groups
     * 
     * @param ?string $method
     * @return \Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function common_text(?string $method): View|RedirectResponse
    {
        return $this->handleTextGroupRequest('core', $method);
    }

    /**
     * Shows the social media common text field groups
     * 
     * @param ?string $method
     * @return \Illuminate\View\View|Illuminate\Http\RedirectResponse
     */
    public function social_media(?string $method): View|RedirectResponse
    {
        return $this->handleTextGroupRequest('social', $method);
    }

    /**
     * Handles a common text group request, performing the 
     * provided method on the group
     * 
     * @param string $group
     * @param ?string $method
     * @return \Illuminate\View\View|Illuminate\Http\RedirectResponse
     * @throws \InvalidArgumentException
     */
    protected function handleTextGroupRequest(string $group, ?string $method): View|RedirectResponse
    {
        if (!isset(CommonTextConfig::$groups[$group])) {
            throw new InvalidArgumentException('The common text group \'' . $group . '\' is not defined.');
        }

        switch ($method) {
            case 'update':
                return $this->updateTextGroup($group);
            default:
                return $this->renderTextGroup($group);
        }
    }

    /**
     * Renders a common text group form
     * 
     * @param string $group
     * @return \Illuminate\View\View
     */
    protected function renderTextGroup(string $group): View
    {
        $route = Route::getCurrentRoute();
        $labelling = CommonTextConfig::$labelling[$group];
        $FormModel = new CommonText();

        return view(
            'common-text.form',
            [
                'title'         => $labelling['title'],
                'description'   => $labelling['description'],
                'form_action'   => $this->Crud->getUrlRoute($route->parameter('section'), 'update', null),
                'FormModel'     => $FormModel,
                'fields'        => $this->generateGroupedFields($group, $FormModel),
                'breadcrumbs'   => [
                    ['url' => route('adm.route'),           'title' => 'Dashboard'],
                    ['url' => route('adm.route', $route->parameter('section')),  'title' => $labelling['title']],
                ]
            ]
        );
    }

    /**
     * Updates a common text group
     * 
     * @param string $group
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function updateTextGroup(string $group): RedirectResponse
    {
        $route = Route::getCurrentRoute();
        $labelling = CommonTextConfig::$labelling[$group];

        $all_fields = $this->generateFlatFields($group);
        foreach ($all_fields as $key => $details) {
            CommonText::updateOrCreate(
                ['field' => $key, 'language_id' => $this->Crud->getLanguageId()],
                ['value' => request()->input($key)]
            );
        }

        $this->Crud->flushCache();

        return response()->redirectTo($this->Crud->getUrlRoute($route->parameter('section'), null, null))
            ->with('success', $labelling['title'] . ' updated successfully.');
    }

    /**
     * Generates field groups for the provided common text
     * group, appling their values to the provided FormModel
     * 
     * @param string $group
     * @param \App\Models\CommonText $FormModel
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function generateGroupedFields(string $group, CommonText $FormModel): array
    {
        $grouped_fields = [];
        $keys_to_load   = [];
        
        foreach (CommonTextConfig::$groups[$group] as $section_heading => $field_keys) {
            $grouped_fields[$section_heading] = [];

            foreach ($field_keys as $field) {
                if (!isset(CommonTextConfig::$fields[$field])) {
                    throw new InvalidArgumentException('The field \'' . $field . '\' is missing from the configuration field set.');
                }

                $keys_to_load[] = $field;
                $grouped_fields[$section_heading][$field] = $this->getFieldDetails($field);
            }
        }

        $this->loadKeyValuesIntoModel($FormModel, $keys_to_load);
        return $grouped_fields;
    }

    /**
     * Generates a flat array of all fields within the provided
     * common text group's field groups
     * 
     * @param string $group
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function generateFlatFields(string $group): array
    {
        $flat_fields = [];

        foreach (CommonTextConfig::$groups[$group] as $field_keys) {
            foreach ($field_keys as $field) {
                if (!isset(CommonTextConfig::$fields[$field])) {
                    throw new InvalidArgumentException('The field \'' . $field . '\' is missing from the configuration field set.');
                }

                $flat_fields[$field] = $this->getFieldDetails($field);
            }
        }

        return $flat_fields;
    }

    /**
     * Returns a field's details
     * 
     * @param string $field
     * @return array
     */
    protected function getFieldDetails(string $field): array
    {
        $field_details = CommonTextConfig::$fields[$field];
        if (!isset($field_details['required'])) {
            $field_details['required'] = false;
        }
        return $field_details;
    }

    /**
     * Loads common text field values into the provided 
     * FormModel
     * 
     * @param \App\Models\CommonText $FormModel
     * @param array $field_keys
     * @return void
     */
    protected function loadKeyValuesIntoModel(CommonText $FormModel, array $field_keys): void
    {
        $StoredValues = CommonText::whereIn('field', $field_keys)->get();
        foreach ($StoredValues as $Value) {
            data_set($FormModel, $Value->field, $Value->value);
        }
    }
}