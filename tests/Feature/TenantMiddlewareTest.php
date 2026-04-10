<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->forgetTenant();
        parent::tearDown();
    }

    private function rootUrl(string $path = '/'): string
    {
        $domain = config('app.root_domain', 'quickshul.com');
        return 'http://' . $domain . '/' . ltrim($path, '/');
    }

    public function test_root_domain_returns_marketing_page(): void
    {
        $this->get($this->rootUrl('/'))
            ->assertOk();
    }

    public function test_known_active_tenant_subdomain_redirects_to_login(): void
    {
        $tenant = $this->createTenant(['slug' => 'myshul']);

        // Unauthenticated user on tenant subdomain → TenantMiddleware binds tenant → route redirects to login
        $this->get($this->tenantUrl($tenant, '/'))
            ->assertRedirect('/login');
    }

    public function test_unknown_subdomain_returns_404(): void
    {
        $domain = config('app.root_domain', 'quickshul.com');

        $this->get('http://doesnotexist.' . $domain . '/')
            ->assertNotFound();
    }

    public function test_suspended_tenant_returns_404(): void
    {
        $tenant = $this->createTenant(['slug' => 'suspended-shul', 'status' => 'suspended']);

        $this->get($this->tenantUrl($tenant, '/'))
            ->assertNotFound();
    }

    public function test_active_tenant_serves_request_while_suspended_returns_404(): void
    {
        $active    = $this->createTenant(['slug' => 'active-shul',    'status' => 'active']);
        $suspended = $this->createTenant(['slug' => 'suspended-shul', 'status' => 'suspended']);

        $this->get($this->tenantUrl($active, '/'))
            ->assertRedirect(); // unauthenticated → redirects to login (not 404)

        $this->get($this->tenantUrl($suspended, '/'))
            ->assertNotFound();
    }
}
