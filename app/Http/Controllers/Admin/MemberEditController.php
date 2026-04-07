<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipType;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyEmail;
use App\Models\FamilyMember;
use App\Models\User;
use App\Services\AuditService;
use App\Services\HebrewDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberEditController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private HebrewDateService $hebrewDate
    ) {}

    public function edit(int $id)
    {
        $family = Family::with(['emails', 'members', 'yahrtzeits'])->findOrFail($id);

        // Map email → user so we can show last login per email row
        $emailAddresses = $family->emails->pluck('email');
        $usersByEmail   = User::whereIn('email', $emailAddresses)
            ->get(['email', 'last_login_at', 'avatar'])
            ->keyBy('email');

        return view('admin.members.edit', [
            'family'          => $family,
            'membershipTypes' => MembershipType::cases(),
            'usersByEmail'    => $usersByEmail,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $family = Family::findOrFail($id);
        $old    = $family->toArray();

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'address'         => 'nullable|string|max:255',
            'city'            => 'nullable|string|max:100',
            'state'           => 'nullable|string|max:2',
            'zip'             => 'nullable|string|max:10',
            'phone'           => 'nullable|string|max:30',
            'membership_type' => 'required|in:' . implode(',', array_column(MembershipType::cases(), 'value')),
            'member_since'    => 'nullable|date',
            'total_pledged'   => 'nullable|numeric|min:0',
            'notes'           => 'nullable|string',
        ]);

        $family->update($validated);
        $this->audit->logModelChange($family, $old, $family->fresh()->toArray());

        return redirect()->route('admin.members.show', $id)->with('success', 'Family updated successfully.');
    }

    public function addMember(Request $request, int $familyId)
    {
        $family = Family::findOrFail($familyId);

        $validated = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'hebrew_name'          => 'nullable|string|max:200',
            'gender'               => 'required|in:male,female,other',
            'role'                 => 'required|in:parent,child,other',
            'date_of_birth'        => 'nullable|date',
            'hebrew_date_of_birth' => 'nullable|string|max:50',
            'hebrew_dob_override'  => 'boolean',
        ]);

        $validated['family_id'] = $familyId;

        if (!empty($validated['date_of_birth']) && empty($validated['hebrew_dob_override'])) {
            $validated['hebrew_date_of_birth'] = $this->hebrewDate->formatForStorage($validated['date_of_birth']);
        }

        $member = FamilyMember::create($validated);
        $this->audit->log('family_member.created', $member, [], $member->toArray(), "Added member {$member->full_name} to {$family->name}");

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Member added.');
    }

    public function editMember(int $familyId, int $mid)
    {
        $family = Family::findOrFail($familyId);
        $member = FamilyMember::where('family_id', $familyId)->findOrFail($mid);

        return view('admin.members.edit-member', compact('family', 'member'));
    }

    public function updateMember(Request $request, int $familyId, int $mid)
    {
        $member = FamilyMember::where('family_id', $familyId)->findOrFail($mid);
        $old    = $member->toArray();

        $validated = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'hebrew_name'          => 'nullable|string|max:200',
            'gender'               => 'required|in:male,female,other',
            'role'                 => 'required|in:parent,child,other',
            'date_of_birth'        => 'nullable|date',
            'hebrew_date_of_birth' => 'nullable|string|max:50',
            'hebrew_dob_override'  => 'boolean',
        ]);

        if (!empty($validated['date_of_birth']) && empty($validated['hebrew_dob_override'])) {
            $validated['hebrew_date_of_birth'] = $this->hebrewDate->formatForStorage($validated['date_of_birth']);
        }

        $member->update($validated);
        $this->audit->logModelChange($member, $old, $member->fresh()->toArray());

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Member updated.');
    }

    public function deleteMember(int $familyId, int $mid)
    {
        $member = FamilyMember::where('family_id', $familyId)->findOrFail($mid);
        $member->delete();
        $this->audit->log('family_member.deleted', $member, $member->toArray(), [], "Soft-deleted member {$member->full_name}");

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Member removed.');
    }

    public function addEmail(Request $request, int $familyId)
    {
        $family = Family::findOrFail($familyId);

        $request->validate([
            'email' => [
                'required', 'email', 'unique:family_emails,email',
                function ($attr, $value, $fail) use ($familyId) {
                    $user = User::where('email', $value)->first();
                    if ($user && $user->family_id && $user->family_id !== $familyId) {
                        $fail('This email belongs to a different family account.');
                    }
                },
            ],
        ]);

        $isPrimary = $family->emails()->count() === 0;

        FamilyEmail::create([
            'family_id'  => $familyId,
            'email'      => $request->email,
            'is_primary' => $isPrimary,
        ]);

        // Create a User account if one doesn't already exist for this email,
        // so the person can log in. Send a password reset email to let them set their password.
        $userCreated = false;
        $existingUser = User::where('email', $request->email)->first();

        if (!$existingUser) {
            User::create([
                'name'      => $family->name,
                'email'     => $request->email,
                'family_id' => $familyId,
                'password'  => Hash::make('Torah613!'),
            ]);
            $userCreated = true;
        } elseif (!$existingUser->family_id) {
            // User exists but isn't linked to a family — link them
            $existingUser->update(['family_id' => $familyId]);
        }

        $this->audit->log('family.email.added', $family, [], ['email' => $request->email], "Added email {$request->email} to {$family->name}");

        $msg = $userCreated
            ? 'Email added and login account created for ' . $request->email . ' (default password set).'
            : 'Email added.';

        return redirect()->route('admin.members.edit', $familyId)->with('success', $msg);
    }

    public function removeEmail(int $familyId, int $eid)
    {
        $family = Family::findOrFail($familyId);
        $email  = FamilyEmail::where('family_id', $familyId)->findOrFail($eid);

        if ($email->is_primary && $family->emails()->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot remove the last primary email.']);
        }

        $addr = $email->email;
        $email->delete();

        // Promote another email to primary if needed
        if ($email->is_primary) {
            $family->emails()->first()?->update(['is_primary' => true]);
        }

        $this->audit->log('family.email.removed', $family, ['email' => $addr], [], "Removed email {$addr} from {$family->name}");

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Email removed.');
    }
}
