<?php

namespace App\Http\Controllers;

use App\Models\Family;
use App\Models\FamilyMember;
use App\Services\HebrewDateService;
use Illuminate\Http\Request;

class MemberProfileController extends Controller
{
    public function __construct(private HebrewDateService $hebrewDate) {}

    public function updateContact(Request $request)
    {
        $family = auth()->user()->family;

        if (!$family) {
            return back()->withErrors(['error' => 'No family account linked.']);
        }

        $validated = $request->validate([
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'zip'     => 'nullable|string|max:10',
        ]);

        $family->update($validated);

        return back()->with('success', 'Contact information updated.');
    }

    public function addMember(Request $request)
    {
        $family = auth()->user()->family;

        if (!$family) {
            return back()->withErrors(['error' => 'No family account linked.']);
        }

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

        if (!empty($validated['date_of_birth']) && empty($validated['hebrew_dob_override'])) {
            $validated['hebrew_date_of_birth'] = $this->hebrewDate->formatForStorage($validated['date_of_birth']);
        }
        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $validated['hebrew_date_of_death'] = $this->hebrewDate->formatForStorage($validated['date_of_death']);
        }

        $validated['family_id'] = $family->id;
        FamilyMember::create($validated);

        return redirect()->route('family')->with('success', 'Family member added successfully.');
    }

    public function updateMember(Request $request, int $mid)
    {
        $family = auth()->user()->family;

        if (!$family) {
            return back()->withErrors(['error' => 'No family account linked.']);
        }

        $member = FamilyMember::where('family_id', $family->id)->findOrFail($mid);

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

        if (!empty($validated['date_of_birth']) && empty($validated['hebrew_dob_override'])) {
            $validated['hebrew_date_of_birth'] = $this->hebrewDate->formatForStorage($validated['date_of_birth']);
        }
        if (!empty($validated['date_of_death']) && empty($validated['hebrew_dod_override'])) {
            $validated['hebrew_date_of_death'] = $this->hebrewDate->formatForStorage($validated['date_of_death']);
        }

        $member->update($validated);

        return redirect()->route('family')->with('success', "{$member->full_name} updated successfully.");
    }
}
