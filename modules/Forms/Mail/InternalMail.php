<?php

namespace Modules\Forms\Mail;

use App\Mail\Mailable;
use Modules\Forms\Models\Form;
use Modules\Forms\Models\FormSubmission;

class InternalMail extends Mailable
{
    protected $blade_view   = 'emails.cms-form.internal';
    protected $text_view    = 'emails.cms-form.internal__plain';

    public function __construct(public Form $Form, public FormSubmission $FormSubmission)
    {
        $this->subject_line = $Form->internal_subject;
    }
}