<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Beneficiary;

class BeneficiaryDisabled extends Mailable
{
    use Queueable, SerializesModels;

    public $beneficiary;

    public function __construct(Beneficiary $beneficiary)
    {
        $this->beneficiary = $beneficiary;
    }

    public function build()
    {
        return $this->markdown('emails.beneficiary-disabled')
                    ->subject('Account Disabled Notification');
    }
}
