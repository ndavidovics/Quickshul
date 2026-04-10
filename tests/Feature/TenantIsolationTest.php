<?php

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User   $userA;
    protected User   $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);

        $this->tenantA = $this->createTenant(['slug' => 'shul-a']);
        $this->tenantB = $this->createTenant(['slug' => 'shul-b']);

        $this->userA = $this->createUser($this->tenantA, ['email' => 'user@shula.com', 'password' => bcrypt('secret')]);
        $this->userB = $this->createUser($this->tenantB, ['email' => 'user@shulb.com', 'password' => bcrypt('secret')]);
    }

    protected function tearDown(): void
    {
        // Clear auth state — the array session driver persists between test methods
        // in the same class since the application is not refreshed between methods.
        auth()->logout();
        $this->forgetTenant();
        parent::tearDown();
    }

    // ── Login isolation ──────────────────────────────────────────────────────

    public function test_user_can_log_in_on_their_own_tenant(): void
    {
        $this->post($this->tenantUrl($this->tenantA, '/login'), [
            'email' => 'user@shula.com', 'password' => 'secret',
        ])->assertRedirect();

        $this->assertAuthenticated();
    }

    public function test_user_cannot_log_in_on_another_tenants_subdomain(): void
    {
        // user@shula.com belongs to tenant A — HasTenant scope on User prevents finding it on tenant B
        $this->post($this->tenantUrl($this->tenantB, '/login'), [
            'email' => 'user@shula.com', 'password' => 'secret',
        ]);

        $this->assertGuest();
    }

    public function test_wrong_password_on_correct_tenant_fails(): void
    {
        $this->post($this->tenantUrl($this->tenantA, '/login'), [
            'email' => 'user@shula.com', 'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_same_email_at_two_tenants_resolves_to_correct_user(): void
    {
        $sharedA = $this->createUser($this->tenantA, [
            'email'    => 'shared@example.com',
            'password' => bcrypt('passwordA'),
        ]);
        $this->createUser($this->tenantB, [
            'email'    => 'shared@example.com',
            'password' => bcrypt('passwordB'),
        ]);

        $this->post($this->tenantUrl($this->tenantA, '/login'), [
            'email' => 'shared@example.com', 'password' => 'passwordA',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($sharedA);
    }

    public function test_tenant_a_password_fails_on_tenant_b_for_shared_email(): void
    {
        $this->createUser($this->tenantA, ['email' => 'shared@example.com', 'password' => bcrypt('passwordA')]);
        $this->createUser($this->tenantB, ['email' => 'shared@example.com', 'password' => bcrypt('passwordB')]);

        // Tenant B has its own password — tenant A's password should NOT work on tenant B
        $this->post($this->tenantUrl($this->tenantB, '/login'), [
            'email' => 'shared@example.com', 'password' => 'passwordA',
        ]);

        $this->assertGuest();
    }

    // ── Data isolation (HasTenant scope) ────────────────────────────────────

    public function test_families_are_scoped_to_current_tenant(): void
    {
        $familyA = Family::withoutGlobalScopes()->forceCreate([
            'tenant_id' => $this->tenantA->id,
            'name'      => 'Cohen',
        ]);
        $familyB = Family::withoutGlobalScopes()->forceCreate([
            'tenant_id' => $this->tenantB->id,
            'name'      => 'Levi',
        ]);

        $this->bindTenant($this->tenantA);
        $families = Family::all();

        $this->assertTrue($families->contains($familyA));
        $this->assertFalse($families->contains($familyB));
    }

    public function test_withoutGlobalScopes_sees_all_tenants_data(): void
    {
        Family::withoutGlobalScopes()->forceCreate(['tenant_id' => $this->tenantA->id, 'name' => 'Cohen']);
        Family::withoutGlobalScopes()->forceCreate(['tenant_id' => $this->tenantB->id, 'name' => 'Levi']);

        $this->bindTenant($this->tenantA);

        $this->assertCount(2, Family::withoutGlobalScopes()->get());
    }

    // ── Admin cross-tenant protection ────────────────────────────────────────

    public function test_admin_cannot_access_another_tenants_admin_area(): void
    {
        $adminA = $this->createUser($this->tenantA, [
            'email'    => 'admin@shula.com',
            'is_admin' => true,
        ]);

        // Manually bind tenant B — simulates adminA arriving at shul-b.quickshul.com/admin
        $this->bindTenant($this->tenantB);

        $this->actingAs($adminA)
            ->get('/admin/members')
            ->assertForbidden();
    }

    public function test_admin_can_access_their_own_tenant_admin_area(): void
    {
        $adminA = $this->createUser($this->tenantA, [
            'email'    => 'admin@shula.com',
            'is_admin' => true,
        ]);

        $this->bindTenant($this->tenantA);

        $this->actingAs($adminA)
            ->get('/admin/members')
            ->assertOk();
    }
}
