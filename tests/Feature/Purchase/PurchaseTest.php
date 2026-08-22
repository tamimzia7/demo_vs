<?php

use App\Models\Purchase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Purchase Management', function () {
    it('can record a purchase', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
            'amount' => 99.99,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('purchases', [
            'visitor_vin' => 'VC-2026-000001',
            'amount' => 99.99,
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can record a purchase with offering reference', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'offering_id' => 42,
            'purchased_at' => now()->toDateTimeString(),
            'amount' => 149.99,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('purchases', [
            'visitor_vin' => 'VC-2026-000001',
            'offering_id' => 42,
            'amount' => 149.99,
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a Purchase timeline event', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
            'amount' => 99.99,
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Purchase',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('advances visitor lifecycle to Purchased on first purchase', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
        ]);

        $visitor->refresh();
        $this->assertEquals('Purchased', $visitor->lifecycle_state);
    });

    it('advances visitor lifecycle to Repeat Customer on second purchase', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->subDay()->toDateTimeString(),
        ]);

        $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
        ]);

        $visitor->refresh();
        $this->assertEquals('Repeat Customer', $visitor->lifecycle_state);
    });

    it('creates a Lifecycle Changed timeline event', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Lifecycle Changed',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can list purchases for a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Purchase::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'amount' => 99.99,
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.purchases.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('can get purchase detail', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $purchase = Purchase::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'amount' => 99.99,
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.purchases.show', [
            'vin' => $visitor->vin,
            'purchaseId' => $purchase->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'amount' => '99.99',
            ],
        ]);
    });

    it('returns 404 for non-existent purchase', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.purchases.show', [
            'vin' => $visitor->vin,
            'purchaseId' => 99999,
        ]));

        $response->assertStatus(404);
    });

    it('returns validation error for missing purchased_at', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'amount' => 99.99,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('purchased_at');
    });

    it('returns validation error for future date', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('purchased_at');
    });

    it('returns empty list for visitor with no purchases', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.purchases.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    });

    it('returns proper API resource shape', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
            'amount' => 99.99,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'visitor_vin',
                'offering_id',
                'amount',
                'purchased_at',
                'created_at',
            ],
        ]);
    });

    it('denies unauthenticated access', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    });

    it('returns purchases ordered by purchased_at desc', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Purchase::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'amount' => 50.00,
            'purchased_at' => now()->subDay(),
        ]);

        Purchase::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'amount' => 100.00,
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.purchases.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $data = $response->json('data');
        $this->assertEquals('100.00', $data[0]['amount']);
        $this->assertEquals('50.00', $data[1]['amount']);
    });

    it('enforces tenant isolation on purchases', function () {
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);
        $user1 = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant1->id]);
        $user2 = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant2->id]);

        Visitor::create([
            'tenant_id' => $tenant1->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Visitor 1',
            'lifecycle_state' => 'Interested',
        ]);

        Visitor::create([
            'tenant_id' => $tenant2->id,
            'vin' => 'VC-2026-000002',
            'name' => 'Visitor 2',
            'lifecycle_state' => 'Interested',
        ]);

        Purchase::create([
            'tenant_id' => $tenant1->id,
            'visitor_vin' => 'VC-2026-000001',
            'amount' => 100.00,
            'purchased_at' => now(),
        ]);

        Purchase::create([
            'tenant_id' => $tenant2->id,
            'visitor_vin' => 'VC-2026-000002',
            'amount' => 200.00,
            'purchased_at' => now(),
        ]);

        $response = $this->actingAs($user1)->getJson(route('visitors.purchases.index', 'VC-2026-000001'));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $data = $response->json('data');
        $this->assertEquals('100.00', $data[0]['amount']);
    });

    it('cannot record purchase for visitor in different tenant', function () {
        $tenant1 = Tenant::create(['name' => 'Tenant 1']);
        $tenant2 = Tenant::create(['name' => 'Tenant 2']);
        $user1 = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant1->id]);

        Visitor::create([
            'tenant_id' => $tenant2->id,
            'vin' => 'VC-2026-000002',
            'name' => 'Visitor 2',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user1)->postJson(route('visitors.purchases.store', 'VC-2026-000002'), [
            'purchased_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(404);
    });

    it('returns visitor lifecycle state after recording purchase', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.purchases.store', $visitor->vin), [
            'purchased_at' => now()->toDateTimeString(),
        ]);

        $response->assertStatus(201);
        $visitor->refresh();
        $this->assertEquals('Purchased', $visitor->lifecycle_state);
    });
});
