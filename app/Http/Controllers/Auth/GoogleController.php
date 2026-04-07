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
        // Pass tenant_id in state so callback can scope the user lookup
        $tenantId = app()->bound('tenant') ? app('tenant')->id : null;
        $state = base64_encode(json_encode(['tenant_id' => $tenantId]));

        return Socialite::driver('google')
            ->with(['state' => $state])
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Decode state to recover tenant_id
        $state    = request()->input('state', '');
        $stateData = $state ? json_decode(base64_decode($state), true) : [];
        $tenantId  = $stateData['tenant_id'] ?? null;

        $query = User::where('email', $googleUser->getEmail());
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        $user = $query->first();

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
