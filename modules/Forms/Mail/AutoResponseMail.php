<?php

namespace Modules\Forms\Mail;

use App\Mail\Mailable;
use App\Models\File;
use Illuminate\Mail\Mailables\Attachment;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

class AutoResponseMail extends Mailable
{
    protected $blade_view   = 'emails.cms-form.auto-response';

    public function __construct(public Form $Form, public FormSubmission $FormSubmission)
    {
        $this->subject_line = $Form->auto_response_subject;
    }

    public function attachments(): array
    {
        if (empty($this->Form->auto_response_attachment) || !isset($this->Form->auto_response_attachment['file_path'])) {
            return [];
        }

        $AttachedFile = File::find($this->Form->auto_response_attachment['file_id']);
        if (!$AttachedFile) {
            return [];
        }

        return [
            Attachment::fromStorage($AttachedFile->storage_path)
                ->as($AttachedFile->title . '.' .$AttachedFile->extension),
        ];
    }
}