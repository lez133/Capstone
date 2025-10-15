<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnpublishNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $beneficiary, $aidProgram, $schedule;

    /**
     * Create a new message instance.
     */
    public function __construct($beneficiary, $aidProgram, $schedule)
    {
        $this->beneficiary = $beneficiary;
        $this->aidProgram = $aidProgram;
        $this->schedule = $schedule;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Unpublish Notification Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.unpublish-notification',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->subject('Schedule Unpublished for ' . $this->aidProgram->aid_program_name)
            ->view('emails.unpublish-notification');
    }
}
