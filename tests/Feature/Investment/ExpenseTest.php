<?php

use App\Models\Expense;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Relationship Investment', function () {
    it('can log an expense', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Travel',
            'amount' => 50.00,
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', [
            'visitor_vin' => 'VC-2026-000001',
            'category' => 'Travel',
            'amount' => 50.00,
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can log an expense without amount', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Phone Call',
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', [
            'visitor_vin' => 'VC-2026-000001',
            'category' => 'Phone Call',
            'amount' => null,
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates an Expense timeline event', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Travel',
            'amount' => 50.00,
            'expense_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'user',
            'source' => 'Expense',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can list expenses for a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Expense::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'category' => 'Travel',
            'amount' => 50.00,
            'expense_date' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.expenses.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('returns empty list for visitor with no expenses', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.expenses.index', $visitor->vin));

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

        $response = $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Travel',
            'amount' => 50.00,
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'visitor_vin',
                'category',
                'amount',
                'expense_date',
                'created_at',
            ],
        ]);
    });

    it('returns validation error for missing category', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'amount' => 50.00,
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category');
    });

    it('returns validation error for missing expense_date', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Travel',
            'amount' => 50.00,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('expense_date');
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

        $response = $this->actingAs($user)->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Travel',
            'expense_date' => now()->addDay()->toDateString(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('expense_date');
    });

    it('denies unauthenticated access', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->postJson(route('visitors.expenses.store', $visitor->vin), [
            'category' => 'Travel',
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertStatus(403);
    });

    it('returns expenses ordered by expense_date desc', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Expense::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'category' => 'Phone Call',
            'expense_date' => now()->subDay(),
        ]);

        Expense::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'category' => 'Travel',
            'expense_date' => now(),
        ]);

        $response = $this->actingAs($user)->getJson(route('visitors.expenses.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $data = $response->json('data');
        $this->assertEquals('Travel', $data[0]['category']);
        $this->assertEquals('Phone Call', $data[1]['category']);
    });
});
