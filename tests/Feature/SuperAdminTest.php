<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User   $regularUser;
    protected User   $adminUser;
    protected User   $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);

        $this->tenant = $this->createTenant(['slug' => 'myshul']);

        $this->regularUser = $this->createUser($this->tenant, [
            'email'    => 'member@myshul.com',
            'is_admin' => false,
        ]);

        $this->adminUser = $this->createUser($this->tenant, [
            'email'    => 'admin@myshul.com',
            'is_admin' => true,
        ]);

        $this->superAdmin = $this->createUser($this->tenant, [
            'email'          => 'super@quickshul.com',
            'is_admin'       => true,
            'is_super_admin' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->forgetTenant();
        parent::tearDown();
    }

    public function test_unauthenticated_user_is_redirected_from_superadmin(): void
    {
        $this->get('/superadmin')->assertRedirect();
    }

    public function test_regular_user_cannot_access_superadmin(): void
    {
        $this->actingAs($this->regularUser)
            ->get('/superadmin')
            ->assertForbidden();
    }

    public function test_tenant_admin_cannot_access_superadmin(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/superadmin')
            ->assertForbidden();
    }

    public function test_super_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/superadmin')
            ->assertOk();
    }

    public function test_super_admin_can_see_tenants_list(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/superadmin')
            ->assertSee($this->tenant->name);
    }

    public function test_super_admin_can_view_tenant_detail(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/superadmin/tenants/' . $this->tenant->id)
            ->assertOk()
            ->assertSee($this->tenant->name);
    }

    public function test_super_admin_can_suspend_tenant(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants/' . $this->tenant->id . '/suspend')
            ->assertRedirect();

        $this->assertEquals('suspended', $this->tenant->fresh()->status);
    }

    public function test_super_admin_can_activate_tenant(): void
    {
        $this->tenant->update(['status' => 'pending']);

        $this->actingAs($this->superAdmin)
            ->post('/superadmin/tenants/' . $this->tenant->id . '/activate')
            ->assertRedirect();

        $this->assertEquals('active', $this->tenant->fresh()->status);
    }

    public function test_super_admin_can_access_health_page(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/superadmin/health')
            ->assertOk();
    }

    public function test_super_admin_can_access_jobs_page(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/superadmin/jobs')
            ->assertOk();
    }
}
