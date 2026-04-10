<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ImpersonateController extends Controller
{
    public function start(int $tenantId)
    {
        $tenant = Tenant::withoutGlobalScopes()->findOrFail($tenantId);

        $admin = User::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_admin', true)
            ->orderBy('id')
            ->first();

        if (!$admin) {
            return back()->withErrors(['error' => 'No admin user found for this tenant.']);
        }

        $token = Str::random(32);

        Cache::put('impersonate_' . $token, [
            'user_id'    => $admin->id,
            'tenant_id'  => $tenantId,
            'return_url' => route('superadmin.tenants.show', $tenantId),
        ], now()->addMinutes(5));

        $url = 'https://' . $tenant->slug . '.' . config('app.root_domain', 'quickshul.com')
             . '/do-impersonate/' . $token;

        return redirect($url);
    }
}
