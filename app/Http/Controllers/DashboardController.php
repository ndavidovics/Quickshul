<?php

namespace App\Http\Controllers;

use App\Services\HebrewDateService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private HebrewDateService $hebrewDate) {}

    public function index(Request $request)
    {
        $user   = auth()->user();
        $family = $user->family?->load(['members', 'emails']);

        $yahrzeits = [];
        $birthdays = [];

        if ($family) {
            $yahrzeits = $this->hebrewDate->upcomingYahrzeits($family->id, 60);
            $birthdays = $this->hebrewDate->upcomingBirthdays($family->id, 60);
        }

        $recentPayments = $family
            ? $family->payments()->completed()->limit(5)->get()
            : collect();

        $openPledges = $family
            ? $family->pledges()->where('status', 'open')->get()
            : collect();

        return view('member.dashboard', compact('user', 'family', 'yahrzeits', 'birthdays', 'recentPayments', 'openPledges'));
    }
}
