<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailReminderJob;
use App\Models\EmailSend;
use App\Models\EmailTemplate;
use App\Models\Family;
use App\Services\AuditService;
use App\Services\EmailReminderService;
use Illuminate\Http\Request;

class EmailReminderController extends Controller
{
    public function __construct(
        private EmailReminderService $emailService,
        private AuditService $audit
    ) {}

    public function index()
    {
        $templates   = EmailTemplate::all();
        $recentSends = EmailSend::with('family')->latest()->limit(50)->get();
        $families    = Family::withBalance()->orderBy('name')->get(['id', 'name', 'outstanding_balance']);

        return view('admin.emails.index', compact('templates', 'recentSends', 'families'));
    }

    public function preview(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'family_id'   => 'required|exists:families,id',
        ]);

        $template = EmailTemplate::findOrFail($request->template_id);
        $family   = Family::findOrFail($request->family_id);
        $preview  = $this->emailService->preview($template, $family);

        return response()->json(['preview' => $preview]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'template_id'     => 'required|exists:email_templates,id',
            'recipient_mode'  => 'required|in:selected,all_with_balance',
            'family_ids'      => 'required_if:recipient_mode,selected|array',
            'family_ids.*'    => 'exists:families,id',
        ]);

        $template = EmailTemplate::findOrFail($request->template_id);

        $families = $request->recipient_mode === 'all_with_balance'
            ? Family::withBalance()->get()
            : Family::whereIn('id', $request->family_ids)->get();

        $count = 0;
        foreach ($families as $family) {
            $primaryEmail = $family->primary_email_string;
            if (!$primaryEmail) continue;

            $rendered = $this->emailService->renderTemplate($template, $family);

            $send = EmailSend::create([
                'template_id'     => $template->id,
                'family_id'       => $family->id,
                'recipient_email' => $primaryEmail,
                'subject'         => $rendered['subject'],
                'body'            => $rendered['body'],
                'status'          => 'pending',
            ]);

            SendEmailReminderJob::dispatch($send->id);
            $count++;
        }

        $this->audit->log('admin.emails.sent', null, [], ['count' => $count, 'template' => $template->name], "Email reminder '{$template->name}' queued for {$count} families");

        return back()->with('success', "{$count} emails queued for delivery.");
    }
}
