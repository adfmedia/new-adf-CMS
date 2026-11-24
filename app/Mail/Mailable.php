<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable as MailMailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

abstract class Mailable extends MailMailable
{
    use Queueable, SerializesModels;

    /**
     * Where the email is being sent from
     * 
     * @var string
     */
    protected $from_address     = null;

    /**
     * Who the email is being sent from
     * 
     * @var string
     */
    protected $from_name        = null;

    /**
     * The email's subject line
     * 
     * @var string 
     */
    protected $subject_line     = null;

    /**
     * The blade file for the HTML version of the email
     * 
     * @var string
     */
    protected $blade_view       = null;

    /**
     * The blade file for the plain-text version of the email
     * 
     * @var string
     */
    protected $text_view        = null;

    /**
     * Generate an 'envelope' for the email, setting the
     * sender address & name, and the subject line
     * 
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address((!empty($this->from_address) ? $this->from_address : config('mail.from.address')), (!empty($this->from_name) ? $this->from_name : config('mail.from.name'))),
            subject: $this->subject_line
        );
    }

    /**
     * Generate the email's contents
     * 
     * @return Content
     */
    public function content(): Content
    {
        if (!empty($this->text_view)) {
            return new Content(
                view: $this->blade_view,
                text: $this->text_view
            );
        }

        return new Content(
            view: $this->blade_view
        );
    }
}