<?php

namespace App\Services;

use App\Models\EmailSend;
use App\Models\Family;
use App\Models\Payment;
use App\Models\PaymentToken;
use App\Enums\EmailSendStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class EmailReminderService
{
    // -------------------------------------------------------------------------
    // Balance Reminder (Email Type 1)
    // -------------------------------------------------------------------------

    public function buildBalanceReminderHtml(Family $family, PaymentToken $token, ?string $introText = null): string
    {
        $greeting     = $this->formatGreeting($family);
        $openPledges  = $family->pledges()->where('status', 'open')->where('balance', '>', 0)->orderByDesc('invoice_date')->get();
        $totalBalance = $openPledges->sum('balance');
        $paymentUrl   = url('/pay/' . $token->token);
        $expiresAt    = $token->expires_at->format('F j, Y');

        $intro = $introText ?? $this->defaultBalanceReminderIntro($family);
        $intro = $this->replaceMergeTags($intro, $family, $greeting, [
            '{balance}'  => '$' . number_format((float)$totalBalance, 2),
            '{pay_link}' => $paymentUrl,
        ]);

        return View::make('emails.balance_reminder', compact(
            'family', 'greeting', 'openPledges', 'totalBalance', 'paymentUrl', 'expiresAt', 'intro'
        ))->render();
    }

    public function buildBalanceReminderSubject(Family $family): string
    {
        return 'Your YIOM Account — Outstanding Balance';
    }

    public function defaultBalanceReminderIntro(Family $family): string
    {
        return "We hope this message finds you and your family well. As a valued member of the Young Israel of Memphis, "
            . "your support is the foundation upon which our community stands — enabling us to maintain a vibrant home "
            . "for Torah learning, meaningful Tefillah, and the beautiful traditions that bind us together.\n\n"
            . "We are writing to kindly bring your attention to the following outstanding pledges on your account. "
            . "We understand that life is busy, and we are grateful for any support you are able to provide.";
    }

    public function formatGreeting(Family $family): string
    {
        // Prefer actual member records (most accurate)
        $parents = $family->members()->where('role', 'parent')->get();
        if ($parents->isEmpty()) {
            $parents = $family->members()->get();
        }

        if ($parents->isNotEmpty()) {
            $lastName   = trim($parents->first()->last_name ?? '');
            $firstNames = $parents->pluck('first_name')->map('trim')->filter()->values();

            if ($firstNames->isNotEmpty()) {
                $first = $firstNames->take(2)->implode(' & ');
                return $lastName ? "{$first} {$lastName}" : $first;
            }
        }

        // Fall back to parsing the display name
        return $this->parseGreetingFromName($family->name);
    }

    private function parseGreetingFromName(string $name): string
    {
        $name = trim($name);

        // "LastName, First & Second" → "First & Second LastName"
        if (str_contains($name, ',')) {
            [$last, $rest] = array_map('trim', explode(',', $name, 2));
            if ($rest) return "{$rest} {$last}";
        }

        // "LastName First & Second" → "First & Second LastName"
        if (str_contains($name, '&') || preg_match('/\band\b/i', $name)) {
            $words = preg_split('/\s+/', $name, 2);
            if (count($words) === 2) {
                return $words[1] . ' ' . $words[0];
            }
        }

        // Organisation or single word — use as-is
        return $name;
    }

    // -------------------------------------------------------------------------
    // Giving Statement (Email Type 2)
    // -------------------------------------------------------------------------

    public function buildGivingStatementHtml(Family $family, Carbon $from, Carbon $to, string $introText): string
    {
        $greeting = $this->formatGreeting($family);

        $payments = Payment::where('family_id', $family->id)
            ->completed()
            ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('payment_date')
            ->get();

        $totalAmount = $payments->sum('amount');
        $periodLabel = $from->format('M j, Y') . ' – ' . $to->format('M j, Y');

        $intro = $this->replaceMergeTags($introText, $family, $greeting, [
            '{period}' => $periodLabel,
            '{total}'  => '$' . number_format((float)$totalAmount, 2),
        ]);

        return View::make('emails.giving_statement', [
            'family'      => $family,
            'greeting'    => $greeting,
            'payments'    => $payments,
            'totalAmount' => $totalAmount,
            'periodLabel' => $periodLabel,
            'introText'   => $intro,
        ])->render();
    }

    private function replaceMergeTags(string $text, Family $family, string $greeting, array $extra = []): string
    {
        return str_replace(
            array_merge(['{greeting}', '{family_name}', '{member_name}'], array_keys($extra)),
            array_merge([$greeting, $family->name, $greeting], array_values($extra)),
            $text
        );
    }

    public function defaultGivingStatementIntro(Carbon $from, Carbon $to): string
    {
        $year = $from->year === $to->year ? $from->year : $from->format('Y') . '–' . $to->format('Y');

        return "Dear {greeting},\n\n"
            . "We are deeply grateful for your generous support of the Young Israel of Memphis during {$year}. "
            . "Your contributions make it possible for our shul to thrive as a center of Torah, Tefillah, and Tradition "
            . "for our entire community.\n\n"
            . "Enclosed please find your giving statement for the period {period}. "
            . "Your total contributions during this period were {total}.\n\n"
            . "This statement may be used for tax purposes. If you have any questions, please do not hesitate to contact our office.";
    }

    // -------------------------------------------------------------------------
    // Sending
    // -------------------------------------------------------------------------

    public function send(EmailSend $emailSend): bool
    {
        try {
            $ccEmails = [];
            if ($emailSend->family_id) {
                $ccEmails = \App\Models\FamilyEmail::where('family_id', $emailSend->family_id)
                    ->where('is_primary', false)
                    ->where('email', '!=', $emailSend->recipient_email)
                    ->pluck('email')
                    ->toArray();
            }

            Mail::html($emailSend->body, function ($message) use ($emailSend, $ccEmails) {
                $message->to($emailSend->recipient_email)
                        ->subject($emailSend->subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
                if ($ccEmails) {
                    $message->cc($ccEmails);
                }
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

    public function sendDirect(string $toEmail, string $subject, string $htmlBody): bool
    {
        try {
            Mail::html($htmlBody, function ($message) use ($toEmail, $subject) {
                $message->to($toEmail)
                        ->subject($subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });
            return true;
        } catch (\Throwable $e) {
            \Log::error('EmailReminderService::sendDirect failed: ' . $e->getMessage());
            return false;
        }
    }
}
