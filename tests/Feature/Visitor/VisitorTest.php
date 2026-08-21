<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use App\Visitors\Services\VisitorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Visitor Management', function () {
    it('can display visitors list', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('visitors.index'));

        $response->assertStatus(200);
        $response->assertSee('Visitors');
    });

    it('can create a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->post(route('visitors.store'), [
            'name' => 'Test Visitor',
            'channel' => 'Website',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('visitors', [
            'name' => 'Test Visitor',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('generates VIN in correct format', function () {
        $service = new VisitorService;
        $vin = $service->generateVin();

        $this->assertMatchesRegularExpression('/^VC-\d{4}-\d{6}$/', $vin);
    });

    it('can view visitor workspace', function () {
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
        $response->assertSee('Test Visitor');
        $response->assertSee('VC-2026-000001');
    });

    it('can archive a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.archive', $visitor->vin));

        $response->assertRedirect();
        $this->assertDatabaseHas('visitors', [
            'id' => $visitor->id,
            'lifecycle_state' => 'Archived',
        ]);
    });

    it('can restore an archived visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Archived',
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('visitors.restore', $visitor->vin));

        $response->assertRedirect();
        $this->assertDatabaseHas('visitors', [
            'id' => $visitor->id,
            'lifecycle_state' => 'Interested',
        ]);
    });

    it('can search visitors by name', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'John Doe',
            'lifecycle_state' => 'Interested',
        ]);

        Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000002',
            'name' => 'Jane Smith',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    });
});
