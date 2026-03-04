<?php

namespace App\Http\Controllers;

use App\Services\HebrewDateService;
use Illuminate\Http\Request;

class FamilyInfoController extends Controller
{
    public function __construct(private HebrewDateService $hebrewDate) {}

    public function index(Request $request)
    {
        $family = auth()->user()->family?->load(['members', 'emails']);

        if (!$family) {
            return view('member.family', ['family' => null, 'membersWithDates' => collect(), 'yahrzeits' => collect(), 'birthdays' => collect()]);
        }

        $membersWithDates = $family->members->map(function ($member) {
            $hebrewDob = null;
            $hebrewDod = null;

            if ($member->date_of_birth && !$member->hebrew_dob_override) {
                $hebrewDob = $this->hebrewDate->gregorianToHebrew($member->date_of_birth);
            } elseif ($member->hebrew_date_of_birth) {
                $hebrewDob = ['formatted' => $member->hebrew_date_of_birth];
            }

            if ($member->date_of_death && !$member->hebrew_dod_override) {
                $hebrewDod = $this->hebrewDate->gregorianToHebrew($member->date_of_death);
            } elseif ($member->hebrew_date_of_death) {
                $hebrewDod = ['formatted' => $member->hebrew_date_of_death];
            }

            return array_merge($member->toArray(), [
                'hebrew_dob_computed' => $hebrewDob,
                'hebrew_dod_computed' => $hebrewDod,
                'model'               => $member,
            ]);
        });

        $yahrzeits = $this->hebrewDate->upcomingYahrzeits($family->id, 60);
        $birthdays = $this->hebrewDate->upcomingBirthdays($family->id, 60);

        return view('member.family', compact('family', 'membersWithDates', 'yahrzeits', 'birthdays'));
    }
}
