<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Authorization', function () {
    it('redirects guests away from the admin area', function () {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    });

    it('allows a company owner into the admin area', function () {
        $user = User::factory()->create([
            'role' => 'company_owner',
            'tenant_id' => Tenant::create(['name' => 'Acme Co'])->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk();
    });

    it('allows a super admin into the admin area', function () {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk();
    });

    it('denies access to a non-admin role', function () {
        // sales_executive is not an active V1 role and must be refused by the
        // role middleware even though the enum permits the value.
        $user = User::factory()->create(['role' => 'sales_executive']);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    });
});
