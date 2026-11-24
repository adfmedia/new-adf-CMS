<?php

namespace Modules\Forms\Adm\Controllers;

use Adm\Http\Controllers\AdmController;
use App\Helpers\TrackingDataHelper;
use Illuminate\Support\Facades\Storage;
use Modules\Forms\Config\FormConfig;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

class FormsController extends AdmController
{
    public function forms($method, $id)
    {
        return $this->Crud->boot(
            $method,
            $id,
            FormConfig::class
        );
    }

    public function form_submissions($form_id, $submission_id = null)
    {
        $Form = Form::find($form_id);
        if (!$Form) {
            return response()->redirectTo(route('adm.route', 'forms'));
        }

        if ($submission_id) {
            $Submission = FormSubmission::where([
                'form_id'   => $form_id,
                'id'        => $submission_id
            ])->first();
            if (!$Submission) {
                return response()->redirectTo(route('adm.route', ['form-submissions', $form_id]));
            }

            if (request()->query('attachment')) {
                foreach ($Submission->fields as $Field) {
                    if ($Field->form_field_id == request()->query('attachment')) {
                        $file = $Field->value;
                        $file_path = storage_path('app/' . $file);
                        if (file_exists($file_path)) {
                            return response()->file($file_path);
                        }
                    }
                }

                return response()->redirectTo(route('adm.route', ['form-submissions', $form_id, $Submission->id]));
            }

            return view(
                'form-submissions.view',
                [
                    'Form'          => $Form,
                    'Submission'    => $Submission,
                    'breadcrumbs'   => [
                        [
                            'url'   => route('adm.route'),
                            'title' => 'Dashboard'
                        ],
                        [
                            'url'   => route('adm.route', ['form-submissions', $form_id]),
                            'title' => 'Form submissions - ' . $Form->title
                        ],
                        [
                            'url'   => route('adm.route', ['form-submissions', $form_id, $submission_id]),
                            'title' => 'Submission ' . $Submission->id
                        ]
                    ]
                ]
            );
        }

        if (request()->query('export')) {
            return $this->exportLogs($Form);
        }
        elseif (request()->query('delete')) {
            $deleted_ids = [];
            foreach ((array)request()->input('delete_items') as $item_id) {
                $Submission = FormSubmission::where([
                    'id'        => $item_id,
                    'form_id'   => $form_id
                ])->first();
                if ($Submission) {
                    $Submission->delete();
                    $deleted_ids[] = $item_id;
                }
            }

            session()->flash('success', sizeof($deleted_ids) . ' submission(s) deleted.');
            return response()->json([
                'success'   => true,
                'message'   => sizeof($deleted_ids) . ' submission(s) deleted.'
            ]);
        }

        $listing_columns = [];
        foreach ($Form->fields as $Field) {
            if (!empty($Field->settings) && isset($Field->settings['show_in_cms_list']) && $Field->settings['show_in_cms_list']) {
                $listing_columns[$Field->id] = $Field->label;
            }
        }
        $Submissions = $Form->form_submissions()->orderBy('created_at', 'desc')->paginate(25);

        return view(
            'form-submissions.list',
            [
                'Form'              => $Form,
                'Submissions'       => $Submissions,
                'listing_columns'   => $listing_columns,
                'export_url'        => route('adm.route', ['form-submissions', $form_id]) . '?export=true',
                'delete_url'        => route('adm.route', ['form-submissions', $form_id]) . '?delete=true',
                'breadcrumbs'       => [
                    [
                        'url'   => route('adm.route'),
                        'title' => 'Dashboard'
                    ],
                    [
                        'url'   => route('adm.route', ['form-submissions', $form_id]),
                        'title' => 'Form submissions - ' . $Form->title
                    ]
                ]
            ]
        );
    }

    protected function exportLogs(Form $Form) 
    {
        return response()->streamDownload(function () use ($Form) {
            $fp = fopen('php://output', 'w');

            $tracking_data_fields = [
                'Entry URL',
                'Referring URL',
                'Referring Source'
            ];
            foreach (config('tracking_data.tracking_parameter_labels') as $param => $label) {
                $tracking_data_fields[] = $label;
            }

            $csv_headings = [];
            foreach ($Form->fields as $Field) {
                $csv_headings[] = $Field->label;
            }
            foreach ($tracking_data_fields as $field) {
                $csv_headings[] = $field;
            }
            $csv_headings[] = 'Submitted on';
            $csv_headings[] = 'Submitted from';
            $csv_headings[] = 'Marketing opt-in';
            $csv_headings[] = 'Marketing opt-in messaging';

            fputcsv($fp, $csv_headings);

            foreach ($Form->form_submissions()->orderBy('created_at', 'desc')->get() as $Submission) {
                $sub_fields = $Submission->fields->pluck('value', 'form_field_id');
                $sub_td = $Submission->tracking_data->pluck('value', 'parameter');

                $this_row = [];
                foreach ($Form->fields as $Field) {
                    $this_row[] = isset($sub_fields[$Field->id]) ? $sub_fields[$Field->id] : '';
                }
                foreach ($tracking_data_fields as $td_label) {
                    $this_row[] = isset($sub_td[$td_label]) ? $sub_td[$td_label] : '';
                }
                $this_row[] = $Submission->created_at->format("Y-m-d H:i:s");
                $this_row[] = $Submission->submission_url;
                $this_row[] = $Submission->marketing_opt_in ? 'Y' : 'N';
                $this_row[] = $Submission->marketing_opt_in_text;

                fputcsv($fp, $this_row);
            }

            fclose($fp);
        }, $Form->title . ' submissions export - ' . date("Y-m-d-h-i-s") . '.csv');
    }
}