<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\BeneficiaryDocument;

class DocumentEnabledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;
    public $reason;

    public function __construct(BeneficiaryDocument $document, $reason = null)
    {
        $this->document = $document;
        $this->reason = $reason;
    }

    public function build()
    {
        return $this->subject('Your document has been enabled')
            ->view('emails.document-enabled');
    }
}
