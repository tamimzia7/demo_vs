<?php

use App\Models\Relationship;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Relationship Management', function () {
    it('can display relationship for a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.relationships.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJson(['data' => null]);
    });

    it('can assign a relationship to a marketer', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $marketer = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.relationships.store', $visitor->vin), [
            'marketer_id' => $marketer->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('relationships', [
            'visitor_vin' => 'VC-2026-000001',
            'marketer_id' => $marketer->id,
            'status' => 'assigned',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can request a transfer', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $marketer1 = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $marketer2 = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Relationship::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'marketer_id' => $marketer1->id,
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.relationships.transfer', $visitor->vin), [
            'target_marketer_id' => $marketer2->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('relationships', [
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'transfer_requested',
            'transferred_from_id' => $marketer1->id,
        ]);
    });

    it('can approve a transfer', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenant->id]);
        $marketer1 = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $marketer2 = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Relationship::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'marketer_id' => $marketer1->id,
            'status' => 'transfer_requested',
            'transferred_from_id' => $marketer1->id,
        ]);

        $response = $this->actingAs($user)->post(route('visitors.relationships.approve', $visitor->vin));

        $response->assertStatus(200);
        $this->assertDatabaseHas('relationships', [
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'transferred',
        ]);
    });

    it('can reject a transfer', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'company_owner', 'tenant_id' => $tenant->id]);
        $marketer1 = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $marketer2 = User::factory()->create(['role' => 'sales_executive', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Relationship::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'marketer_id' => $marketer1->id,
            'status' => 'transfer_requested',
            'transferred_from_id' => $marketer1->id,
        ]);

        $response = $this->actingAs($user)->post(route('visitors.relationships.reject', $visitor->vin));

        $response->assertStatus(200);
        $this->assertDatabaseHas('relationships', [
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'rejected',
            'transferred_from_id' => null,
        ]);
    });

    it('can display workspace with relationship panel', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.workspace', $visitor->vin));

        $response->assertStatus(200);
        $response->assertSee('Relationship Center');
        $response->assertSee('No relationship assigned');
    });

    it('cannot assign relationship to non-existent marketer', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.relationships.store', $visitor->vin), [
            'marketer_id' => 99999,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('marketer_id');
    });
});
