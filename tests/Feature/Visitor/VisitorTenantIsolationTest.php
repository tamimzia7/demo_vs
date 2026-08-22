<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Visitor tenant isolation', function () {
    it('prevents a user from opening another tenant visitor workspace', function () {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $ownerA = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantA->id]);
        $visitorB = Visitor::create([
            'tenant_id' => $tenantB->id,
            'vin' => 'VC-2026-999999',
            'name' => 'Other Tenant',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($ownerA)
            ->get(route('visitors.workspace', $visitorB->vin))
            ->assertNotFound();
    });

    it('does not list another tenant visitors in the index', function () {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        $ownerA = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenantA->id]);
        Visitor::create([
            'tenant_id' => $tenantA->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Ours',
            'lifecycle_state' => 'Interested',
        ]);
        Visitor::create([
            'tenant_id' => $tenantB->id,
            'vin' => 'VC-2026-000002',
            'name' => 'Theirs',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($ownerA)->get(route('visitors.index'));

        $response->assertSee('Ours');
        $response->assertDontSee('Theirs');
    });

    it('records a VisitorCreated system timeline event on create', function () {
        $tenant = Tenant::create(['name' => 'Acme']);
        $user = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenant->id]);

        $this->actingAs($user)->post(route('visitors.store'), [
            'name' => 'New Visitor',
            'channel' => 'Website',
        ]);

        $this->assertDatabaseHas('visitors', [
            'name' => 'New Visitor',
            'tenant_id' => $tenant->id,
        ]);

        $visitor = Visitor::where('name', 'New Visitor')->first();

        $this->assertDatabaseHas('timeline_events', [
            'tenant_id' => $tenant->id,
            'visitor_vin' => $visitor->vin,
            'type' => 'system',
            'source' => 'VisitorCreated',
        ]);
    });

    it('allows the same VIN number in different tenants', function () {
        $tenantA = Tenant::create(['name' => 'Tenant A']);
        $tenantB = Tenant::create(['name' => 'Tenant B']);

        Visitor::create([
            'tenant_id' => $tenantA->id,
            'vin' => 'VC-2026-000001',
            'name' => 'A',
            'lifecycle_state' => 'Interested',
        ]);

        Visitor::create([
            'tenant_id' => $tenantB->id,
            'vin' => 'VC-2026-000001',
            'name' => 'B',
            'lifecycle_state' => 'Interested',
        ]);

        expect(Visitor::where('tenant_id', $tenantA->id)->count())->toBe(1);
        expect(Visitor::where('tenant_id', $tenantB->id)->count())->toBe(1);
    });
});
