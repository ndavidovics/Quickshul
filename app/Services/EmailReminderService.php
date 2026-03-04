<?php

namespace App\Services;

use App\Enums\EmailSendStatus;
use App\Models\EmailSend;
use App\Models\EmailTemplate;
use App\Models\Family;
use Illuminate\Support\Facades\Mail;

class EmailReminderService
{
    public function renderTemplate(EmailTemplate $template, Family $family): array
    {
        $mergeFields = $this->buildMergeFields($family);

        $subject = str_replace(array_keys($mergeFields), array_values($mergeFields), $template->subject);
        $body    = str_replace(array_keys($mergeFields), array_values($mergeFields), $template->body);

        return compact('subject', 'body');
    }

    public function preview(EmailTemplate $template, Family $family): string
    {
        return $this->renderTemplate($template, $family)['body'];
    }

    public function send(EmailSend $emailSend): bool
    {
        try {
            Mail::html($emailSend->body, function ($message) use ($emailSend) {
                $message->to($emailSend->recipient_email)
                        ->subject($emailSend->subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $emailSend->update([
                'status'  => EmailSendStatus::Sent,
                'sent_at' => now(),
                'error'   => null,
            ]);

            return true;
        } catch (\Throwable $e) {
            $emailSend->update([
                'status' => EmailSendStatus::Failed,
                'error'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function buildPayPalLink(Family $family): string
    {
        return url('/financial?family_id=' . $family->id);
    }

    private function buildMergeFields(Family $family): array
    {
        $primaryMember = $family->members()->where('role', 'parent')->first()
            ?? $family->members()->first();

        return [
            '{family_name}' => $family->name,
            '{balance}'     => '$' . number_format((float)$family->outstanding_balance, 2),
            '{paypal_link}' => $this->buildPayPalLink($family),
            '{member_name}' => $primaryMember ? $primaryMember->first_name : $family->name,
        ];
    }
}
