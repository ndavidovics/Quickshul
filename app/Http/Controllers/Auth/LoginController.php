<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        if (Auth::check()) {
            return redirect($this->redirectAfterLogin(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // On a tenant subdomain, scope the login to that tenant only
        if (app()->bound('tenant')) {
            $tenant = app('tenant');
            $user = \App\Models\User::where('email', $credentials['email'])
                ->where('tenant_id', $tenant->id)
                ->first();

            if (!$user || !\Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'These credentials do not match our records.',
                ])->onlyInput('email');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended($this->redirectAfterLogin($user));
        }

        // Root domain login (registration flow / superadmin)
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::user()->update(['last_login_at' => now()]);
            return redirect()->intended($this->redirectAfterLogin(Auth::user()));
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->onlyInput('email');
    }

    private function redirectAfterLogin(\App\Models\User $user): string
    {
        if ($user->is_super_admin) {
            return route('superadmin.index');
        }
        if ($user->is_admin && ! $user->family_id) {
            return route('admin.members');
        }
        return '/dashboard';
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
