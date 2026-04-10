<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        $tenantId = app()->bound('tenant') ? app('tenant')->id : null;

        // Store tenant_id server-side so the callback can't be forged via state param
        session(['google_oauth_tenant_id' => $tenantId]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Read tenant_id from session (set before the OAuth redirect)
        $tenantId = session()->pull('google_oauth_tenant_id');

        if (! $tenantId) {
            // No session — OAuth flow started without a valid tenant context
            return redirect('/login')->withErrors([
                'email' => 'Sign-in session expired. Please try again.',
            ]);
        }

        $user = User::where('email', $googleUser->getEmail())
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $user) {
            return redirect('/login')->withErrors([
                'email' => 'No account found for that Google address. Please contact the office.',
            ]);
        }

        // Keep google_id and avatar up to date
        $user->update([
            'google_id'     => $googleUser->getId(),
            'avatar'        => $googleUser->getAvatar(),
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        return redirect($this->redirectAfterLogin($user));
    }

    private function redirectAfterLogin(User $user): string
    {
        if ($user->is_admin && ! $user->family_id) {
            return route('admin.members');
        }
        return '/dashboard';
    }
}
