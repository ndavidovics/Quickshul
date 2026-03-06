<?php

namespace App\Http\Controllers;

use App\Models\MemberApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MemberApplicationController extends Controller
{
    public function show()
    {
        return view('apply.form');
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'membership_type'                  => 'required|in:full_family,associate,single,first_year_free',
            'family_name'                      => 'required|string|max:255',
            'address'                          => 'nullable|string|max:255',
            'city'                             => 'nullable|string|max:100',
            'state'                            => 'nullable|string|max:2',
            'zip'                              => 'nullable|string|max:10',
            'phone'                            => 'nullable|string|max:30',
            'emails'                           => 'required|array|min:1',
            'emails.*'                         => 'required|email|max:255',
            'members'                          => 'required|array|min:1',
            'members.*.first_name'             => 'required|string|max:100',
            'members.*.last_name'              => 'required|string|max:100',
            'members.*.gender'                 => 'required|in:male,female,other',
            'members.*.role'                   => 'required|in:parent,child',
            'members.*.date_of_birth'          => 'nullable|date',
            'members.*.hebrew_name'            => 'nullable|string|max:200',
            'notes'                            => 'nullable|string|max:2000',
        ]);

        $application = MemberApplication::create([
            'status'          => 'pending',
            'membership_type' => $validated['membership_type'],
            'data'            => [
                'family_name' => $validated['family_name'],
                'address'     => $validated['address'] ?? null,
                'city'        => $validated['city'] ?? null,
                'state'       => $validated['state'] ?? null,
                'zip'         => $validated['zip'] ?? null,
                'phone'       => $validated['phone'] ?? null,
                'emails'      => $validated['emails'],
                'members'     => $validated['members'],
                'notes'       => $validated['notes'] ?? null,
            ],
        ]);

        // Notify exec@yiom.org
        try {
            $label = $application->membershipLabel();
            $name  = $validated['family_name'];
            Mail::raw(
                "A new membership application has been submitted.\n\n"
                . "Family: {$name}\n"
                . "Membership: {$label}\n"
                . "Primary Email: {$validated['emails'][0]}\n\n"
                . "Review it at: " . route('admin.applications.show', $application->id),
                fn($msg) => $msg->to('exec@yiom.org')
                                ->subject("New Membership Application — {$name}")
            );
        } catch (\Throwable $e) {
            \Log::warning('Failed to send application notification: ' . $e->getMessage());
        }

        return redirect()->route('apply.thank-you');
    }

    public function thankYou()
    {
        return view('apply.thank-you');
    }
}
