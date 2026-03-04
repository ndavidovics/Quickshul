<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MembershipType;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyEmail;
use App\Models\FamilyMember;
use App\Services\AuditService;
use App\Services\HebrewDateService;
use Illuminate\Http\Request;

class MemberEditController extends Controller
{
    public function __construct(
        private AuditService $audit,
        private HebrewDateService $hebrewDate
    ) {}

    public function edit(int $id)
    {
        $family = Family::with(['emails', 'members'])->findOrFail($id);

        return view('admin.members.edit', [
            'family'          => $family,
            'membershipTypes' => MembershipType::cases(),
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
            'date_of_death'        => 'nullable|date',
            'hebrew_date_of_death' => 'nullable|string|max:50',
            'hebrew_dod_override'  => 'boolean',
        ]);

        $validated['family_id'] = $familyId;

        // Auto-compute Hebrew dates if not overridden
        if (!empty($validated['date_of_birth']) && empty($validated['hebrew_dob_override'])) {
            $validated['hebrew_date_of_birth'] = $this->hebrewDate->formatForStorage($validated['date_of_birth']);
        }
        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $validated['hebrew_date_of_death'] = $this->hebrewDate->formatForStorage($validated['date_of_death']);
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
            'date_of_death'        => 'nullable|date',
            'hebrew_date_of_death' => 'nullable|string|max:50',
            'hebrew_dod_override'  => 'boolean',
        ]);

        // Recompute Hebrew dates if date changed and no override
        if (!empty($validated['date_of_birth']) && empty($validated['hebrew_dob_override'])) {
            $validated['hebrew_date_of_birth'] = $this->hebrewDate->formatForStorage($validated['date_of_birth']);
        }
        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $validated['hebrew_date_of_death'] = $this->hebrewDate->formatForStorage($validated['date_of_death']);
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
            'email' => 'required|email|unique:family_emails,email',
        ]);

        $isPrimary = $family->emails()->count() === 0;

        FamilyEmail::create([
            'family_id'  => $familyId,
            'email'      => $request->email,
            'is_primary' => $isPrimary,
        ]);

        $this->audit->log('family.email.added', $family, [], ['email' => $request->email], "Added email {$request->email} to {$family->name}");

        return redirect()->route('admin.members.edit', $familyId)->with('success', 'Email added.');
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
