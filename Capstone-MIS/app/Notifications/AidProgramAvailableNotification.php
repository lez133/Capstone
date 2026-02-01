<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AidProgramAvailableNotification extends Notification
{
    use Queueable;

    protected $program;
    public function __construct($program) { $this->program = $program; }

    public function via($notifiable) { return ['database']; }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'New Aid Program Available',
            'message' => "The program \"{$this->program->aid_program_name}\" is open. You may apply now.",
            'url'     => route('beneficiaries.apply', $this->program->id),
            'program_id' => $this->program->id,
        ];
    }
}
