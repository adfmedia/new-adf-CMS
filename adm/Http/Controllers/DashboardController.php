<?php

namespace Adm\Http\Controllers;

use Adm\Models\ChangeLog;
use App\Helpers\ClassFinder;
use Illuminate\Support\Facades\Auth;
use Modules\Forms\Config\FormConfig;
use Modules\Forms\Models\FormSubmission;

class DashboardController extends AdmController
{
    public function index()
    {
        return view(
            'dashboard.index',
            [
                'forms_enabled'     => $this->formsEnabled(),
                'form_submissions'  => $this->getFormSubmissions(),
                'latest_changes'    => $this->getLatestChanges(),
            ]
        );
    }

    protected function formsEnabled(): bool
    {
        if (!in_array('Forms', config('modules.available'))) {
            return false;
        }

        /** @var \Adm\Models\AdmUser */
        $User = Auth::guard('adm')->user();
        if (!$User->canAccess(FormConfig::$url_slug)) {
            return false;
        }

        return true;
    }

    protected function getFormSubmissions(): array
    {
        $submissions = [];

        foreach (FormSubmission::whereHas('form')->orderBy('created_at', 'desc')->limit(10)->get() as $Submission) {
            $this_record = [
                'url'       => route('adm.route', ['form-submissions', $Submission->form_id, $Submission->id]),
                'form'      => $Submission->form->title,
                'date'      => $Submission->created_at->format('jS F Y, g:ia'),
                'fields'    => []
            ];

            $listing_columns = [];
            foreach ($Submission->form->fields as $Field) {
                if (!empty($Field->settings) && isset($Field->settings['show_in_cms_list']) && $Field->settings['show_in_cms_list']) {
                    $listing_columns[$Field->id] = $Field->label;
                }
            }
            if (!empty($listing_columns)) {
                $listing_field_ids = array_keys($listing_columns);
                $this_record['fields'] = $Submission->fields()
                    ->whereIn('form_field_id', $listing_field_ids)
                    ->pluck('value', 'form_field_id')
                    ->toArray();
            }

            $submissions[] = $this_record;
        }

        return $submissions;
    }

    protected function getLatestChanges(): array
    {
        $changes = [];

        foreach (ChangeLog::orderBy('created_at', 'desc')->limit(10)->get() as $Change) {
            if (!$Change->changeable) { continue; }

            $ChangeableConfig = ClassFinder::findConfigClassForModel($Change->changeable_type);
            if (!$ChangeableConfig) { continue; }

            $name = 'Unknown';
            if ($Change->adm_user) {
                $name_parts = explode(' ', $Change->adm_user->name);
                $fname = array_shift($name_parts);
                $lname = end($name_parts);

                $name = $fname . ' ' . substr($lname, 0, 1) . '.';
            }

            $changes[] = [
                'title'     => $Change->changeable->title,
                'url'       => route('adm.route', [$ChangeableConfig->getStaticPropertyValue('url_slug'), 'edit', $Change->changeable_id]),
                'has_url'   => empty($Change->changeable->deleted_at),
                'section'   => $ChangeableConfig->getStaticPropertyValue('title'),
                'user'      => $name,
                'type'      => $Change->change_type,
                'date'      => $Change->created_at->format('jS M, g:ia')
            ];
        }

        return $changes;
    }
}