<?php

use App\Admin\Services\AdminService;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Tenant isolation', function () {
    it('prevents a company owner from seeing users of other tenants', function () {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $ownerA = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantA->id]);
        User::factory()->count(3)->create(['role' => 'company_owner', 'tenant_id' => $tenantA->id]);
        User::factory()->count(2)->create(['role' => 'company_owner', 'tenant_id' => $tenantB->id]);

        $this->actingAs($ownerA);

        $visible = User::all();

        expect($visible->contains($ownerA))->toBeTrue();
        expect($visible->where('tenant_id', $tenantB->id)->count())->toBe(0);
        expect($visible->where('tenant_id', $tenantA->id)->count())->toBe(4);
    });

    it('grants a super admin global visibility across tenants', function () {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantA->id]);
        User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantB->id]);

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);

        expect(User::count())->toBe(3); // two scoped users + the super admin
    });

    it('scopes the admin user listing service by tenant', function () {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $ownerA = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantA->id]);
        User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantB->id]);

        $this->actingAs($ownerA);

        $users = (new AdminService)->getUsers();

        expect($users->where('tenant_id', $tenantB->id)->count())->toBe(0);
        expect($users->where('tenant_id', $tenantA->id)->count())->toBe(1);
    });
});
