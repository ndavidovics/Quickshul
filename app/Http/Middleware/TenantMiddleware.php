<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    :mixed
    {
        $host = $request->getHost();
        $rootDomain = config('app.root_domain', 'quickshul.com');

        // Extract subdomain
        if (!str_ends_with($host, '.' . $rootDomain)) {
            // Root domain request — no tenant context (registration, marketing, superadmin)
            return $next($request);
        }

        $slug = str_replace('.' . $rootDomain, '', $host);

        $tenant = Tenant::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('status', '!=', 'suspended')
            ->first();

        if (!$tenant) {
            abort(404, 'Shul portal not found.');
        }

        // Bind tenant to container
        app()->instance('tenant', $tenant);

        // Override config with tenant values at runtime
        config([
            'app.name'          => $tenant->name,
            'app.timezone'      => $tenant->timezone,
            'mail.from.address' => $tenant->org_email ?? 'noreply@quickshul.com',
            'mail.from.name'    => $tenant->name,
        ]);

        // Set timezone for this request
        date_default_timezone_set($tenant->timezone);

        return $next($request);
    }
}
