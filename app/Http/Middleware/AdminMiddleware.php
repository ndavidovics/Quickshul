<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            return redirect('/dashboard');
        }

        // If no tenant is bound (root domain request), redirect to the user's tenant subdomain
        if (!app()->bound('tenant')) {
            $user = auth()->user();
            if ($user->tenant_id) {
                $tenant = \App\Models\Tenant::find($user->tenant_id);
                if ($tenant) {
                    $subdomain = 'https://' . $tenant->slug . '.' . config('app.root_domain');
                    return redirect($subdomain . $request->getRequestUri());
                }
            }
            abort(403, 'No tenant context.');
        }

        // Verify the admin belongs to the bound tenant (prevent cross-tenant admin access)
        $user = auth()->user();
        if (!$user->is_super_admin && $user->tenant_id !== app('tenant')->id) {
            abort(403, 'You do not have admin access to this organization.');
        }

        return $next($request);
    }
}
