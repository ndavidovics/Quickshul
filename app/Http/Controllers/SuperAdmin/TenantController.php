<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Notifications\AdminLoginInvite;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withoutGlobalScopes()
            ->withTrashed()
            ->withCount(['families', 'users'])
            ->orderByDesc('created_at')
            ->paginate(25);

        // Cross-tenant summary stats
        $globalStats = [
            'total_active'   => Tenant::withoutGlobalScopes()->where('status', 'active')->count(),
            'total_families' => \App\Models\Family::withoutGlobalScopes()->count(),
            'total_users'    => \App\Models\User::withoutGlobalScopes()->count(),
            'total_payments' => \App\Models\Payment::withoutGlobalScopes()->count(),
            'new_this_month' => Tenant::withoutGlobalScopes()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // Which tenants on this page have ever completed a QB sync
        $tenantIds      = $tenants->pluck('id')->toArray();
        $syncedTenantIds = \App\Models\QbSyncLog::withoutGlobalScopes()
            ->whereIn('tenant_id', $tenantIds)
            ->pluck('tenant_id')
            ->unique()
            ->toArray();

        return view('superadmin.tenants.index', compact('tenants', 'globalStats', 'syncedTenantIds'));
    }

    public function show(int $id)
    {
        $tenant = Tenant::withoutGlobalScopes()->withTrashed()->findOrFail($id);

        $stats = [
            'families' => \App\Models\Family::withoutGlobalScopes()->where('tenant_id', $id)->count(),
            'users'    => \App\Models\User::withoutGlobalScopes()->where('tenant_id', $id)->count(),
            'payments' => \App\Models\Payment::withoutGlobalScopes()->where('tenant_id', $id)->count(),
        ];

        $admins = \App\Models\User::withoutGlobalScopes()
            ->where('tenant_id', $id)
            ->where('is_admin', true)
            ->orderByDesc('last_login_at')
            ->get(['id', 'name', 'email', 'last_login_at']);

        return view('superadmin.tenants.show', compact('tenant', 'stats', 'admins'));
    }

    public function activate(int $id)
    {
        Tenant::withoutGlobalScopes()->findOrFail($id)->update(['status' => 'active']);
        return back()->with('success', 'Tenant activated.');
    }

    public function suspend(int $id)
    {
        Tenant::withoutGlobalScopes()->findOrFail($id)->update(['status' => 'suspended']);
        return back()->with('success', 'Tenant suspended.');
    }

    public function destroy(int $id)
    {
        Tenant::withoutGlobalScopes()->findOrFail($id)->delete();
        return redirect()->route('superadmin.index')->with('success', 'Tenant deleted.');
    }

    public function sendInvite(int $tenantId, int $userId)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenantId);

        $user = \App\Models\User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('id', $userId)
            ->where('is_admin', true)
            ->firstOrFail();

        // Create a reset token directly on the user (bypasses email lookup,
        // so duplicate emails across tenants are handled correctly)
        $token = app(\Illuminate\Auth\Passwords\PasswordBrokerManager::class)
            ->broker()
            ->createToken($user);

        $loginUrl = 'https://' . $tenant->slug . '.' . config('app.root_domain', 'quickshul.com')
            . '/reset-password/' . $token
            . '?email=' . urlencode($user->email);

        $user->notify(new AdminLoginInvite($loginUrl, $tenant->name));

        return back()->with('success', "Login invite sent to {$user->email}.");
    }
}
