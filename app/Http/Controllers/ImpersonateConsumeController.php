<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ImpersonateConsumeController extends Controller
{
    public function consume(Request $request, string $token)
    {
        $data = Cache::pull('impersonate_' . $token);

        if (!$data) {
            abort(403, 'Invalid or expired impersonation token. Tokens expire after 5 minutes.');
        }

        // Verify we're on the correct tenant subdomain
        if (!app()->bound('tenant') || app('tenant')->id !== (int) $data['tenant_id']) {
            abort(403, 'Tenant mismatch.');
        }

        $user = User::withoutGlobalScopes()->findOrFail($data['user_id']);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('impersonating', true);
        $request->session()->put('impersonation_return_url', $data['return_url']);

        return redirect()->route('admin.members');
    }
}
