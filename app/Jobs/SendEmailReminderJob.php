<?php

namespace App\Jobs;

use App\Models\EmailSend;
use App\Services\EmailReminderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $emailSendId) {}

    public function handle(EmailReminderService $service): void
    {
        $emailSend = EmailSend::find($this->emailSendId);

        if (!$emailSend) {
            return;
        }

        $service->send($emailSend);
    }
}
