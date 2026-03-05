<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSend;
use App\Models\Family;
use App\Models\PaymentToken;
use App\Services\AuditService;
use App\Services\EmailReminderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmailReminderController extends Controller
{
    public function __construct(
        private EmailReminderService $emailService,
        private AuditService $audit
    ) {}

    public function index(Request $request)
    {
        $recentSends         = EmailSend::with('family')->latest()->limit(50)->get();
        $familiesWithBalance = Family::withBalance()->orderBy('name')->get(['id', 'name', 'outstanding_balance']);
        $allFamilies         = Family::orderBy('name')->get(['id', 'name', 'outstanding_balance']);

        $defaultBalanceIntro   = $this->emailService->defaultBalanceReminderIntro(
            $familiesWithBalance->first() ?? Family::first()
        );
        $defaultStatementIntro = $this->emailService->defaultGivingStatementIntro(
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        // Support ?tab=balance&family_id=X from member page redirect
        $activeTab       = $request->query('tab', 'balance');
        $preselectFamily = (int)$request->query('family_id', 0);

        return view('admin.emails.index', compact(
            'recentSends', 'familiesWithBalance', 'allFamilies',
            'defaultBalanceIntro', 'defaultStatementIntro',
            'activeTab', 'preselectFamily'
        ));
    }

    // -------------------------------------------------------------------------
    // Balance Reminder
    // -------------------------------------------------------------------------

    public function sendBalanceReminder(Request $request)
    {
        $request->validate([
            'recipient_mode' => 'required|in:all_with_balance,selected',
            'family_ids'     => 'required_if:recipient_mode,selected|array',
            'family_ids.*'   => 'exists:families,id',
            'intro_text'     => 'nullable|string|max:4000',
        ]);

        $families = $request->recipient_mode === 'all_with_balance'
            ? Family::withBalance()->get()
            : Family::whereIn('id', $request->family_ids)->get();

        $count = 0;
        foreach ($families as $family) {
            $primaryEmail = $family->primary_email_string;
            if (! $primaryEmail) continue;

            $openPledges = $family->pledges()->where('status', 'open')->where('balance', '>', 0)->count();
            if (! $openPledges) continue;

            $token   = PaymentToken::generateFor($family);
            $subject = $this->emailService->buildBalanceReminderSubject($family);
            $body    = $this->emailService->buildBalanceReminderHtml($family, $token, $request->intro_text ?: null);

            $send = EmailSend::create([
                'family_id'       => $family->id,
                'recipient_email' => $primaryEmail,
                'subject'         => $subject,
                'body'            => $body,
                'status'          => 'pending',
            ]);

            $this->emailService->send($send);
            $count++;
        }

        $this->audit->log('admin.emails.balance_reminder.sent', null, [], ['count' => $count], "Balance reminder sent to {$count} families");

        return back()->with('success', "{$count} balance reminder email(s) sent.");
    }

    public function redirectToMemberEmail(int $id)
    {
        return redirect()->route('admin.emails', ['tab' => 'balance', 'family_id' => $id]);
    }

    public function sendBalanceReminderTest(Request $request)
    {
        $request->validate([
            'family_id'  => 'required|exists:families,id',
            'intro_text' => 'nullable|string|max:4000',
        ]);

        $family     = Family::findOrFail($request->family_id);
        $adminEmail = auth()->user()->email;

        $token   = PaymentToken::generateFor($family);
        $subject = '[TEST] ' . $this->emailService->buildBalanceReminderSubject($family);
        $body    = $this->emailService->buildBalanceReminderHtml($family, $token, $request->intro_text ?: null);

        $sent = $this->emailService->sendDirect($adminEmail, $subject, $body);

        if ($sent) {
            return response()->json(['success' => "Test email sent to {$adminEmail} with a live payment link."]);
        }

        return response()->json(['error' => 'Failed to send test email.'], 500);
    }

    // -------------------------------------------------------------------------
    // Giving Statement
    // -------------------------------------------------------------------------

    public function sendGivingStatement(Request $request)
    {
        $request->validate([
            'recipient_mode' => 'required|in:all_families,selected',
            'family_ids'     => 'required_if:recipient_mode,selected|array',
            'family_ids.*'   => 'exists:families,id',
            'date_from'      => 'required|date',
            'date_to'        => 'required|date|after_or_equal:date_from',
            'intro_text'     => 'required|string|max:4000',
        ]);

        $from = Carbon::parse($request->date_from)->startOfDay();
        $to   = Carbon::parse($request->date_to)->endOfDay();

        $families = $request->recipient_mode === 'all_families'
            ? Family::orderBy('name')->get()
            : Family::whereIn('id', $request->family_ids)->get();

        $count = 0;
        foreach ($families as $family) {
            $primaryEmail = $family->primary_email_string;
            if (! $primaryEmail) continue;

            $body    = $this->emailService->buildGivingStatementHtml($family, $from, $to, $request->intro_text);
            $subject = 'Your YIOM Giving Statement — ' . $from->format('Y');

            $send = EmailSend::create([
                'family_id'       => $family->id,
                'recipient_email' => $primaryEmail,
                'subject'         => $subject,
                'body'            => $body,
                'status'          => 'pending',
            ]);

            $this->emailService->send($send);
            $count++;
        }

        $this->audit->log('admin.emails.statement.sent', null, [], ['count' => $count], "Giving statements sent to {$count} families");

        return back()->with('success', "{$count} giving statement(s) sent.");
    }

    public function sendGivingStatementTest(Request $request)
    {
        $request->validate([
            'date_from'  => 'required|date',
            'date_to'    => 'required|date|after_or_equal:date_from',
            'intro_text' => 'required|string|max:4000',
        ]);

        $from = Carbon::parse($request->date_from)->startOfDay();
        $to   = Carbon::parse($request->date_to)->endOfDay();

        $adminEmail  = auth()->user()->email;
        $adminFamily = auth()->user()->family ?? Family::first();

        $body    = $this->emailService->buildGivingStatementHtml($adminFamily, $from, $to, $request->intro_text);
        $subject = '[TEST] Your YIOM Giving Statement — ' . $from->format('Y');

        $sent = $this->emailService->sendDirect($adminEmail, $subject, $body);

        if ($sent) {
            return response()->json(['success' => "Test email sent to {$adminEmail}."]);
        }

        return response()->json(['error' => 'Failed to send test email. Check mail configuration.'], 500);
    }

    // -------------------------------------------------------------------------
    // Preview (AJAX)
    // -------------------------------------------------------------------------

    public function previewBalance(Request $request)
    {
        $request->validate([
            'family_id'  => 'required|exists:families,id',
            'intro_text' => 'nullable|string|max:4000',
        ]);

        $family = Family::findOrFail($request->family_id);
        // Generate a real token so the payment link in the preview is actually clickable
        $token  = PaymentToken::generateFor($family);

        $html = $this->emailService->buildBalanceReminderHtml($family, $token, $request->intro_text ?: null);
        return response()->json(['html' => $html]);
    }

    public function previewStatement(Request $request)
    {
        $request->validate([
            'family_id'  => 'required|exists:families,id',
            'date_from'  => 'required|date',
            'date_to'    => 'required|date',
            'intro_text' => 'nullable|string|max:4000',
        ]);

        $family = Family::findOrFail($request->family_id);
        $from   = Carbon::parse($request->date_from)->startOfDay();
        $to     = Carbon::parse($request->date_to)->endOfDay();
        $intro  = $request->intro_text ?? $this->emailService->defaultGivingStatementIntro($from, $to);

        $html = $this->emailService->buildGivingStatementHtml($family, $from, $to, $intro);
        return response()->json(['html' => $html]);
    }
}
