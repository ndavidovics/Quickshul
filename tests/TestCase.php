<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Create an active tenant. */
    protected function createTenant(array $attrs = []): Tenant
    {
        static $counter = 0;
        $counter++;

        return Tenant::create(array_merge([
            'name'             => "Test Shul {$counter}",
            'slug'             => "test-shul-{$counter}",
            'status'           => 'active',
            'timezone'         => 'UTC',
            'onboarding_step'  => 4,
        ], $attrs));
    }

    /** Create a user belonging to a tenant (bypasses global scope). */
    protected function createUser(Tenant $tenant, array $attrs = []): User
    {
        return User::withoutGlobalScopes()->forceCreate(array_merge([
            'tenant_id' => $tenant->id,
            'name'      => 'Test User',
            'email'     => 'user@example.com',
            'password'  => bcrypt('password'),
            'is_admin'  => false,
        ], $attrs));
    }

    /** Bind a tenant into the container (for unit tests that don't go through HTTP). */
    protected function bindTenant(Tenant $tenant): void
    {
        app()->instance('tenant', $tenant);
    }

    /** Remove any tenant binding from the container. */
    protected function forgetTenant(): void
    {
        app()->forgetInstance('tenant');
    }

    /**
     * Absolute URL on a tenant's subdomain.
     * Use this instead of withServerVariables() — Symfony::Request::create() overrides
     * HTTP_HOST with the host extracted from the URI, so passing a full URL is the only
     * reliable way to test with a specific subdomain in Laravel's HTTP test client.
     */
    protected function tenantUrl(Tenant $tenant, string $path = '/'): string
    {
        $domain = config('app.root_domain', 'quickshul.com');
        return 'http://' . $tenant->slug . '.' . $domain . '/' . ltrim($path, '/');
    }
}
