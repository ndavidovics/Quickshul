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
        $tenantSlug = app()->bound('tenant') ? app('tenant')->slug : null;

        // Pass tenant slug via state param — session-based storage breaks because
        // Google's callback lands on the root domain (quickshul.com), not the tenant
        // subdomain, so the tenant's session cookie is inaccessible at callback time.
        return Socialite::driver('google')
            ->with(['state' => $tenantSlug ?? ''])
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Recover tenant slug from the state param we set in redirect()
        $tenantSlug = request()->input('state');

        $tenantId = null;
        if ($tenantSlug) {
            $tenant = \App\Models\Tenant::where('slug', $tenantSlug)->first();
            $tenantId = $tenant?->id;
        }

        if (! $tenantId) {
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
