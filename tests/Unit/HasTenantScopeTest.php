<?php

namespace Tests\Unit;

use App\Models\Family;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasTenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenantA = $this->createTenant(['slug' => 'scope-test-a']);
        $this->tenantB = $this->createTenant(['slug' => 'scope-test-b']);
    }

    protected function tearDown(): void
    {
        $this->forgetTenant();
        parent::tearDown();
    }

    private function seedFamilies(): void
    {
        Family::withoutGlobalScopes()->forceCreate(['tenant_id' => $this->tenantA->id, 'name' => 'Cohen']);
        Family::withoutGlobalScopes()->forceCreate(['tenant_id' => $this->tenantA->id, 'name' => 'Levy']);
        Family::withoutGlobalScopes()->forceCreate(['tenant_id' => $this->tenantB->id, 'name' => 'Goldberg']);
    }

    public function test_query_returns_only_current_tenants_records(): void
    {
        $this->seedFamilies();
        $this->bindTenant($this->tenantA);

        $families = Family::all();

        $this->assertCount(2, $families);
        $this->assertTrue($families->every(fn($f) => $f->tenant_id === $this->tenantA->id));
    }

    public function test_query_returns_only_other_tenant_records_when_bound(): void
    {
        $this->seedFamilies();
        $this->bindTenant($this->tenantB);

        $families = Family::all();

        $this->assertCount(1, $families);
        $this->assertEquals('Goldberg', $families->first()->name);
    }

    public function test_withoutGlobalScopes_returns_all_tenants_records(): void
    {
        $this->seedFamilies();
        $this->bindTenant($this->tenantA);

        $all = Family::withoutGlobalScopes()->get();

        $this->assertCount(3, $all);
    }

    public function test_no_tenant_bound_returns_all_records(): void
    {
        // By design: TenantScope adds no WHERE clause when no tenant is bound.
        // This is intentional for CLI commands and root-domain requests.
        $this->seedFamilies();
        // Do NOT bind any tenant

        $families = Family::all();

        $this->assertCount(3, $families);
    }

    public function test_creating_model_auto_sets_tenant_id(): void
    {
        $this->bindTenant($this->tenantA);

        $family = Family::create(['name' => 'Auto-Tenant Test']);

        $this->assertEquals($this->tenantA->id, $family->tenant_id);
    }

    public function test_tenant_isolation_count(): void
    {
        $this->seedFamilies();

        $this->bindTenant($this->tenantA);
        $this->assertEquals(2, Family::count());

        $this->bindTenant($this->tenantB);
        $this->assertEquals(1, Family::count());
    }

    public function test_switching_tenant_binding_changes_results(): void
    {
        $this->seedFamilies();

        $this->bindTenant($this->tenantA);
        $namesA = Family::pluck('name')->sort()->values()->toArray();

        $this->bindTenant($this->tenantB);
        $namesB = Family::pluck('name')->sort()->values()->toArray();

        $this->assertEquals(['Cohen', 'Levy'], $namesA);
        $this->assertEquals(['Goldberg'], $namesB);
    }
}
