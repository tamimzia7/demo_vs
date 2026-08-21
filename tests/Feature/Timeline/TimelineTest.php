<?php

use App\Models\Tenant;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\Visitor;
use App\Timeline\Services\TimelineService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Timeline Management', function () {
    it('can display timeline for a visitor', function () {
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
        $response->assertSee('Timeline');
        $response->assertSee('No activity yet');
    });

    it('can list timeline events via API', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        TimelineEvent::create([
            'evn' => 'EVN-2026-000001',
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Visitor Created',
            'summary' => 'Visitor was created',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.timeline.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('displays events newest first', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $event1 = TimelineEvent::create([
            'evn' => 'EVN-2026-000001',
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Visitor Created',
            'summary' => 'Visitor was created',
            'created_at' => now()->subHour(),
        ]);

        $event2 = TimelineEvent::create([
            'evn' => 'EVN-2026-000002',
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'user',
            'source' => 'Call',
            'summary' => 'Logged a call',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('visitors.timeline.index', $visitor->vin));

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('EVN-2026-000002', $data[0]['evn']);
        $this->assertEquals('EVN-2026-000001', $data[1]['evn']);
    });

    it('can get event detail via API', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        TimelineEvent::create([
            'evn' => 'EVN-2026-000001',
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Visitor Created',
            'summary' => 'Visitor was created',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.timeline.show', [
            'vin' => $visitor->vin,
            'evn' => 'EVN-2026-000001',
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'evn' => 'EVN-2026-000001',
                'type' => 'system',
                'source' => 'Visitor Created',
            ],
        ]);
    });

    it('can filter events by type', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        TimelineEvent::create([
            'evn' => 'EVN-2026-000001',
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Visitor Created',
            'summary' => 'Visitor was created',
        ]);

        TimelineEvent::create([
            'evn' => 'EVN-2026-000002',
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'user',
            'source' => 'Call',
            'summary' => 'Logged a call',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.timeline.index', [
            'vin' => $visitor->vin,
            'type' => 'system',
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('generates EVN in correct format', function () {
        $service = new TimelineService;
        $evn = $service->generateEvn();

        $this->assertMatchesRegularExpression('/^EVN-\d{4}-\d{6}$/', $evn);
    });

    it('returns 404 for non-existent event', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.timeline.show', [
            'vin' => $visitor->vin,
            'evn' => 'EVN-2026-999999',
        ]));

        $response->assertStatus(404);
    });
});
