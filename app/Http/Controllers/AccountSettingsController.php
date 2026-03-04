<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountSettingsController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $family = $user->family?->load('emails');

        return view('member.settings', compact('user', 'family'));
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        if ($user->google_id && !$user->password) {
            return back()->withErrors(['error' => 'Google accounts cannot set a portal password.']);
        }

        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => $request->password]);

        return back()->with('success', 'Password updated successfully.');
    }
}
