<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\BeneficiaryDocument;

class DocumentVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $document;

    public function __construct(BeneficiaryDocument $document)
    {
        $this->document = $document;
    }

    public function build()
    {
        return $this->subject('Your document has been verified')
            ->view('emails.document-verified');
    }
}
