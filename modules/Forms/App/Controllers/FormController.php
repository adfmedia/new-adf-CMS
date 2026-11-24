<?php

namespace Modules\Forms\App\Controllers;

use App\Helpers\TrackingDataHelper;
use App\Http\Controllers\Controller;
use App\Library\Captcha;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Forms\Mail\AutoResponseMail;
use Modules\Forms\Mail\InternalMail;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;
use Modules\Forms\Models\FormSubmissionField;
use Modules\Forms\Models\FormSubmissionTrackingData;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FormController extends Controller
{
    /**
     * Handles form submission requests
     * 
     * @return JsonResponse
     * @throws NotFoundHttpException
     * @throws ValidationException
     */
    public function submit(): JsonResponse
    {
        $Form = Form::find(request()->input('form_id'));
        if (!$Form) {
            throw new NotFoundHttpException();
        }

        $rules = $messages = [];
        foreach ($Form->fields as $Field) {
            $field_validation = $Field->validation;

            $rules[$Field->form_field_name] = $field_validation['rules'];
            $messages[$Field->form_field_name] = $field_validation['messages'];
        }

        $this->validate(
            request(),
            $rules,
            $messages
        );

        if ($Form->use_recaptcha) {
            list ($success, $score, $response) = Captcha::verify(request()->input('g-recaptcha-response'));
            if (!$success) {
                throw ValidationException::withMessages([
                    'captcha'   => 'Invalid response from captcha'
                ]);
            }
            if (!Captcha::validateScore($score)) {
                throw ValidationException::withMessages([
                    'captcha'   => 'Invalid score from captcha'
                ]);
            }
        }

        $Submission = FormSubmission::create([
            'form_id'               => $Form->id,
            'submission_url'        => request()->input('current_url'),
            'marketing_opt_in'      => request()->input('opt_in', 0),
            'marketing_opt_in_text' => implode(' ', array_filter([$Form->marketing_opt_in_text, $Form->marketing_opt_out_text])),
            'marketing_opt_in_date' => request()->input('opt_in') ? date("Y-m-d h:i:s") : null,
        ]);
        foreach ($Form->ordered_flow_fields as $Field) {
            if ($Field->type == 'file') {
                $value = $this->storeFileUpload($Field->form_field_name);
            } else {
                $value = request()->input($Field->form_field_name);
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
            }

            FormSubmissionField::create([
                'form_submission_id'    => $Submission->id,
                'form_field_id'         => $Field->id,
                'label'                 => $Field->label,
                'value'                 => $value
            ]);
        }

        $TrackingDataHelper = new TrackingDataHelper();
        foreach ($TrackingDataHelper->getTrackingData() as $parameter => $value) {
            FormSubmissionTrackingData::create([
                'form_submission_id'    => $Submission->id,
                'parameter'             => $parameter,
                'value'                 => $value
            ]);
        }

        $this->sendInternalNotifications($Form, $Submission);
        if ($Form->send_auto_response) {
            $this->sendAutoResponseNotification($Form, $Submission);
        }

        $redirect = false;
        if (!empty($Form->redirect_link) && !empty($Form->redirect_link['url'])) {
            $redirect = $Form->redirect_link['url'];
        }

        return response()->json([
            'success'   => true,
            'ga4_event' => $Form->ga4_event,
            'redirect'  => $redirect
        ]);
    }

    /**
     * Stores an uploaded file within the application
     * storage system
     * 
     * @param string $field_name    The file input field name uploaded to
     * @return string|false
     */
    protected function storeFileUpload(string $field_name): string|false
    {
        $File = request()->file($field_name);
        if (!$File) { return false; }

        // Preserve the original filename, but add the current date as a suffix
        $filename = $File->getClientOriginalName();
        $parts = explode('.', $filename);
        $ext = array_pop($parts);
        $filename = implode('.', $parts);
        $filename = \Illuminate\Support\Str::slug($filename . '-' . time());
        $filename .= '.' . $ext;

        return Storage::putFileAs('cms_form_uploads', $File, $filename);
    }

    /**
     * Sends an internal notification for a form submission
     * 
     * @param Form $Form
     * @param FormSubmission $FormSubmission
     * @return void
     */
    protected function sendInternalNotifications(Form $Form, FormSubmission $FormSubmission): void
    {
        try {
            $recipients = $Form->internal_recipients;
            if (empty($recipients)) {
                $recipients = [];
            } else {
                $recipients = explode(',', $recipients);
            }

            if (!empty($recipients)) {
                foreach ($recipients as $idx => $address) {
                    $recipients[$idx] = trim($address);
                }
            }

            $recipients = array_filter($recipients, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            });

            foreach ($recipients as $recipient_email) {
                Mail::to(trim($recipient_email))->send(new InternalMail($Form, $FormSubmission));
            }
        } catch (Exception $e) {
            Log::error('CMS Form internal notification failed to send.');
            Log::error($e);
        }
    }

    /**
     * Sends a notification to the user completing a form
     * assuming there's an email field type present
     * 
     * @param Form $Form
     * @param FormSubmission $FormSubmission
     * @return void
     */
    protected function sendAutoResponseNotification(Form $Form, FormSubmission $FormSubmission): void
    {
        if (empty($Form->auto_response_subject) || empty($Form->auto_response_content)) { return; }
        
        try {
            $recipient_email = null;
            foreach ($Form->fields as $Field) {
                if (!is_null($recipient_email)) { continue; }
                if ($Field->type != 'email') { continue; }

                foreach ($FormSubmission->fields as $SubField) {
                    if ($SubField->form_field_id == $Field->id && !empty($SubField->value)) {
                        $recipient_email = $SubField->value;
                    }
                }
            }

            Mail::to($recipient_email)->send(new AutoResponseMail($Form, $FormSubmission));
        } catch (Exception $e) {
            Log::error('CMS Form auto-response notification failed to send.');
            Log::error($e);
        }
    }
}