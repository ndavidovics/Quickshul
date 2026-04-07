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

        $yahrtzeits = collect();
        $birthdays  = collect();

        if ($family) {
            $yahrtzeits = $this->hebrewDate->upcomingYahrzeits($family->id, 60);
            $upcomingHebrew = $this->hebrewDate->upcomingBirthdays($family->id, 60);
            
            // Enhance birthday data with actual Gregorian birthday
            $birthdays = $upcomingHebrew->map(function ($item) {
                $item['actual_gregorian_date'] = $item['member']->date_of_birth;
                return $item;
            });
        }

        $recentPayments = $family
            ? $family->payments()->completed()->limit(5)->get()
            : collect();

        $openPledges = $family
            ? $family->pledges()->where('status', 'open')->get()
            : collect();

        $paidPast12Months = $family
            ? $family->payments()->completed()
                ->where('payment_date', '>=', now()->subYear())
                ->sum('amount')
            : 0;

        return view('member.dashboard', compact('user', 'family', 'yahrtzeits', 'birthdays', 'recentPayments', 'openPledges', 'paidPast12Months'));
    }
}
