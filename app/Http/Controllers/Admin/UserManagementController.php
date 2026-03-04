<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function __construct(private AuditService $audit) {}

    public function index()
    {
        $users = User::with('family')->orderBy('name')->paginate(50);

        return view('admin.users.index', compact('users'));
    }

    public function toggleAdmin(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot change your own admin status.']);
        }

        $wasAdmin = $user->is_admin;
        $user->update(['is_admin' => !$wasAdmin]);

        $action = $wasAdmin ? 'removed' : 'granted';
        $this->audit->log('admin.user.admin_toggle', $user, ['is_admin' => $wasAdmin], ['is_admin' => !$wasAdmin], "Admin role {$action} for user {$user->name}");

        return back()->with('success', "Admin role {$action} for {$user->name}.");
    }
}
