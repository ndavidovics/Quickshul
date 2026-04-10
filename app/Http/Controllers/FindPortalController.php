<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PlatformMailService;
use Illuminate\Http\Request;

class FindPortalController extends Controller
{
    public function show()
    {
        return view('find-portal');
    }

    public function submit(Request $request, PlatformMailService $mailer)
    {
        $request->validate(['email' => 'required|email']);

        $email = strtolower(trim($request->email));

        // Look up across all tenants — no tenant scope active on root domain
        $users = User::withoutGlobalScopes()
            ->where('email', $email)
            ->with('tenant')
            ->get()
            ->filter(fn($u) => $u->tenant && $u->tenant->status === 'active');

        // Always respond the same way — don't reveal whether the email exists
        if ($users->isNotEmpty()) {
            $portals = $users->map(fn($u) => [
                'name' => $u->tenant->name,
                'url'  => $u->tenant->portalUrl() . '/login',
            ])->values()->all();

            $html = view('emails.find_portal', compact('portals', 'email'))->render();

            $mailer->send($email, 'Your QuickShul Portal Link', $html);
        }

        return back()->with('sent', true);
    }
}
