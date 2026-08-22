<?php

use App\Models\Offering;
use App\Models\Purchase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Offerings\Services\OfferingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Offering Management', function () {
    it('can display offerings list', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('offerings.index'));

        $response->assertStatus(200);
        $response->assertSee('Offerings Catalog');
    });

    it('can create an offering', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->postJson(route('offerings.store'), [
            'name' => 'Test Offering',
            'metadata' => ['category' => 'Software', 'version' => '1.0'],
            'active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('offerings', [
            'name' => 'Test Offering',
            'tenant_id' => $tenant->id,
        ]);

        $offering = Offering::where('tenant_id', $tenant->id)->first();
        $this->assertMatchesRegularExpression('/^OFF-\d{4}-\d{6}$/', $offering->off);
    });

    it('can update an offering', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $offering = Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Original Offering',
            'metadata' => ['category' => 'Software'],
            'active' => true,
        ]);

        $response = $this->actingAs($user)->putJson(route('offerings.update', $offering->off), [
            'name' => 'Updated Offering',
            'active' => false,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('offerings', [
            'id' => $offering->id,
            'name' => 'Updated Offering',
            'active' => false,
        ]);
    });

    it('can soft delete an offering', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $offering = Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Test Offering',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->delete(route('offerings.destroy', $offering->off));

        $response->assertRedirect();
        $this->assertSoftDeleted('offerings', [
            'id' => $offering->id,
        ]);
    });

    it('requires name for offering creation', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->postJson(route('offerings.store'), [
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    });

    it('can search offerings by name', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Software License',
            'active' => true,
        ]);

        Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000002',
            'name' => 'Consulting Service',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('offerings.index', ['search' => 'Software']));

        $response->assertStatus(200);
        $response->assertSee('Software License');
        $response->assertDontSee('Consulting Service');
    });

    it('can filter offerings by active status', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Active Offering',
            'active' => true,
        ]);

        Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000002',
            'name' => 'Inactive Offering',
            'active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('offerings.index', ['active' => 'true']));

        $response->assertStatus(200);
        $response->assertSee('Active Offering');
        $response->assertDontSee('Inactive Offering');
    });

    it('generates off in correct format', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $this->actingAs($user)->postJson(route('offerings.store'), [
            'name' => 'Test Offering',
        ]);

        $offering = Offering::where('tenant_id', $tenant->id)->first();
        $this->assertMatchesRegularExpression('/^OFF-\d{4}-\d{6}$/', $offering->off);
    });

    it('enforces tenant isolation on offerings list', function () {
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);
        $user1 = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant1->id]);

        Offering::create([
            'tenant_id' => $tenant1->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Tenant 1 Offering',
            'active' => true,
        ]);

        Offering::create([
            'tenant_id' => $tenant2->id,
            'off' => 'OFF-2026-000002',
            'name' => 'Tenant 2 Offering',
            'active' => true,
        ]);

        $response = $this->actingAs($user1)->get(route('offerings.index'));

        $response->assertStatus(200);
        $response->assertSee('Tenant 1 Offering');
        $response->assertDontSee('Tenant 2 Offering');
    });

    it('returns 404 for non-existent offering on edit', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('offerings.edit', 'OFF-2026-999999'));

        $response->assertStatus(404);
    });

    it('returns 404 when updating non-existent offering', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->putJson(route('offerings.update', 'OFF-2026-999999'), [
            'name' => 'Updated',
        ]);

        $response->assertStatus(404);
    });

    it('denies access to unauthenticated users', function () {
        $response = $this->get(route('offerings.index'));

        $response->assertStatus(401);
    });

    it('enforces tenant isolation on offering update', function () {
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);
        $user1 = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant1->id]);

        $offering = Offering::create([
            'tenant_id' => $tenant2->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Tenant 2 Offering',
            'active' => true,
        ]);

        $response = $this->actingAs($user1)->putJson(route('offerings.update', $offering->off), [
            'name' => 'Hacked Offering',
        ]);

        $response->assertStatus(404);
    });

    it('can view offering edit form', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $offering = Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Test Offering',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('offerings.edit', $offering->off));

        $response->assertStatus(200);
        $response->assertSee('Edit Offering');
        $response->assertSee('Test Offering');
    });

    it('associates offering with visitor through purchases', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $offering = Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Test Product',
            'active' => true,
        ]);

        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Purchase::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'offering_id' => $offering->id,
            'amount' => 99.99,
            'purchased_at' => now(),
        ]);

        $service = new OfferingService;
        $visitors = $service->getVisitorsForOffering($offering->off, $tenant->id);

        $this->assertCount(1, $visitors);
        $this->assertEquals('VC-2026-000001', $visitors->first()->vin);
    });

    it('returns offerings for a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $offering = Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Test Product',
            'active' => true,
        ]);

        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Purchase::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'offering_id' => $offering->id,
            'amount' => 99.99,
            'purchased_at' => now(),
        ]);

        $service = new OfferingService;
        $offerings = $service->getOfferingsForVisitor('VC-2026-000001', $tenant->id);

        $this->assertCount(1, $offerings);
        $this->assertEquals('OFF-2026-000001', $offerings->first()->off);
    });
});

describe('Offering API', function () {
    it('can list offerings via API', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'API Offering',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('api.offerings.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['off', 'name', 'metadata', 'active', 'created_at', 'updated_at'],
            ],
        ]);
    });

    it('can create offering via API', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->postJson(route('api.offerings.store'), [
            'name' => 'API Created Offering',
            'metadata' => ['category' => 'Software'],
            'active' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => ['off', 'name', 'metadata', 'active'],
        ]);
        $this->assertDatabaseHas('offerings', [
            'name' => 'API Created Offering',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can update offering via API', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $offering = Offering::create([
            'tenant_id' => $tenant->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Original',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->patchJson(route('api.offerings.update', $offering->off), [
            'name' => 'Updated via API',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'data' => ['name' => 'Updated via API'],
        ]);
    });

    it('returns 422 for missing name on API create', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->postJson(route('api.offerings.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    });

    it('enforces tenant isolation on API', function () {
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);
        $user1 = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant1->id]);

        Offering::create([
            'tenant_id' => $tenant2->id,
            'off' => 'OFF-2026-000001',
            'name' => 'Tenant 2 Offering',
            'active' => true,
        ]);

        $response = $this->actingAs($user1)->getJson(route('api.offerings.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    });

    it('returns proper API resource shape', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->postJson(route('api.offerings.store'), [
            'name' => 'Shape Test Offering',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'off',
                'name',
                'metadata',
                'active',
                'created_at',
                'updated_at',
            ],
        ]);
    });

    it('denies API access to unauthenticated users', function () {
        $response = $this->getJson(route('api.offerings.index'));

        $response->assertStatus(401);
    });
});
