<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Visitor authorization', function () {
    it('redirects a guest away from the visitors list', function () {
        $this->get(route('visitors.index'))->assertRedirect(route('login'));
    });

    it('allows a company owner to manage visitors', function () {
        $tenant = Tenant::create(['name' => 'Acme']);
        $user = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenant->id]);

        $this->actingAs($user)->get(route('visitors.index'))->assertOk();
        $this->actingAs($user)->get(route('visitors.create'))->assertOk();
    });

    it('forbids a non-admin role from viewing the visitors list', function () {
        // sales_executive is not an active V1 role (BDR-020); must be denied.
        $tenant = Tenant::create(['name' => 'Acme']);
        $user = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);

        $this->actingAs($user)->get(route('visitors.index'))->assertForbidden();
    });

    it('forbids a non-admin role from creating a visitor', function () {
        $tenant = Tenant::create(['name' => 'Acme']);
        $user = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);

        $this->actingAs($user)->post(route('visitors.store'), [
            'name' => 'Nope',
        ])->assertForbidden();
    });

    it('forbids a non-admin role from archiving a visitor', function () {
        $tenant = Tenant::create(['name' => 'Acme']);
        $user = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'X',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->post(route('visitors.archive', $visitor->vin))->assertForbidden();
    });
});
