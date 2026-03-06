<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipType;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyEmail;
use App\Models\FamilyMember;
use App\Models\MemberApplication;
use App\Models\User;
use App\Services\AuditService;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApplicationController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private QuickBooksService $qbService
    ) {}

    public function index(Request $request)
    {
        $query = MemberApplication::with('reviewer')->latest();

        $status = $request->get('status', 'pending');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $applications = $query->paginate(30)->withQueryString();

        return view('admin.applications.index', compact('applications', 'status'));
    }

    public function show(int $id)
    {
        $application = MemberApplication::with(['reviewer', 'family'])->findOrFail($id);

        return view('admin.applications.show', compact('application'));
    }

    public function approve(Request $request, int $id)
    {
        $application = MemberApplication::findOrFail($id);

        if (!$application->isPending()) {
            return back()->withErrors(['error' => 'This application has already been reviewed.']);
        }

        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $data = $application->data;

        // 1. Create Family
        $family = Family::create([
            'name'            => $data['family_name'],
            'address'         => $data['address'] ?? null,
            'city'            => $data['city'] ?? null,
            'state'           => $data['state'] ?? null,
            'zip'             => $data['zip'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'membership_type' => $application->membership_type,
            'member_since'    => now(),
            'notes'           => $data['notes'] ?? null,
        ]);

        // 2. Create FamilyMembers
        foreach ($data['members'] ?? [] as $m) {
            FamilyMember::create([
                'family_id'     => $family->id,
                'first_name'    => $m['first_name'],
                'last_name'     => $m['last_name'],
                'gender'        => $m['gender'],
                'role'          => $m['role'],
                'date_of_birth' => $m['date_of_birth'] ?? null,
                'hebrew_name'   => $m['hebrew_name'] ?? null,
            ]);
        }

        // 3. Create FamilyEmails + User accounts
        $firstEmail = true;
        foreach ($data['emails'] ?? [] as $email) {
            FamilyEmail::create([
                'family_id'  => $family->id,
                'email'      => $email,
                'is_primary' => $firstEmail,
            ]);
            $firstEmail = false;

            $existing = User::where('email', $email)->first();
            if (!$existing) {
                User::create([
                    'name'      => $data['family_name'],
                    'email'     => $email,
                    'family_id' => $family->id,
                    'password'  => Hash::make('Torah613!'),
                ]);
            } elseif (!$existing->family_id) {
                $existing->update(['family_id' => $family->id]);
            }
        }

        // 4. Mark application approved
        $application->update([
            'status'      => 'approved',
            'family_id'   => $family->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        $this->audit->log('application.approved', $family, [], $family->toArray(), "Approved membership application for {$family->name}");

        // 5. Push to QuickBooks
        $qbMsg = '';
        if ($this->qbService->isConnected()) {
            try {
                $this->qbService->createCustomer($family);
                $family->update(['qb_last_sync_at' => now()]);
                $qbMsg = ' QuickBooks customer created.';
            } catch (\Throwable $e) {
                \Log::error("QB create failed after application approval for family {$family->id}: " . $e->getMessage());
                $qbMsg = ' (QuickBooks push failed — push manually from the member page.)';
            }
        } else {
            $qbMsg = ' QuickBooks is not connected — push manually when ready.';
        }

        return redirect()->route('admin.members.show', $family->id)
            ->with('success', "{$family->name} approved and added to the member directory.{$qbMsg}");
    }

    public function reject(Request $request, int $id)
    {
        $application = MemberApplication::findOrFail($id);

        if (!$application->isPending()) {
            return back()->withErrors(['error' => 'This application has already been reviewed.']);
        }

        $request->validate(['admin_notes' => 'nullable|string|max:2000']);

        $application->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        $this->audit->log('application.rejected', null, [], ['application_id' => $id], "Rejected membership application for {$application->data['family_name']}");

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application rejected.');
    }
}
