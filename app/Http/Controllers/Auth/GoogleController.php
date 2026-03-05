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
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if (! $user) {
            return redirect('/login')->withErrors([
                'email' => 'No account found for that Google address. Please contact the office.',
            ]);
        }

        // Keep google_id and avatar up to date
        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar'    => $googleUser->getAvatar(),
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
