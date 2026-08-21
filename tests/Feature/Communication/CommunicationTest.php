<?php

use App\Models\Communication;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Communication Management', function () {
    it('can send an SMS communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'sms',
            'content' => 'Hello, this is a test SMS.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('communications', [
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'sms',
            'content' => 'Hello, this is a test SMS.',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can send an email communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'email',
            'content' => 'Meeting follow-up email.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('communications', [
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'email',
            'content' => 'Meeting follow-up email.',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can log a call communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'call',
            'content' => 'Discussed pricing options.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('communications', [
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'call',
            'content' => 'Discussed pricing options.',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can log a meeting communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'meeting',
            'content' => 'On-site product demo completed.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('communications', [
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'meeting',
            'content' => 'On-site product demo completed.',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a system event for SMS communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'sms',
            'content' => 'Test SMS message.',
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'SMS Sent',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a system event for email communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'email',
            'content' => 'Test email message.',
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Email Sent',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a user event for call communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'call',
            'content' => 'Test call log.',
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'user',
            'source' => 'Call',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a user event for meeting communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'meeting',
            'content' => 'Test meeting log.',
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'user',
            'source' => 'Meeting',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can list communications for a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Communication::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'sms',
            'content' => 'Test SMS.',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('visitors.communications.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('can get communication detail', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $communication = Communication::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'sms',
            'content' => 'Test SMS.',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('visitors.communications.show', [
            'vin' => $visitor->vin,
            'communicationId' => $communication->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'channel' => 'sms',
                'content' => 'Test SMS.',
            ],
        ]);
    });

    it('returns 404 for non-existent communication', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.communications.show', [
            'vin' => $visitor->vin,
            'communicationId' => 99999,
        ]));

        $response->assertStatus(404);
    });

    it('returns validation error for invalid channel', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'invalid',
            'content' => 'Some content.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('channel');
    });

    it('returns validation error for missing content on SMS', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'sms',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('content');
    });

    it('returns validation error for missing content on email', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('content');
    });

    it('returns validation error for missing content on call', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->postJson(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'call',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('content');
    });

    it('allows empty content for meeting channel', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'meeting',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('communications', [
            'visitor_vin' => 'VC-2026-000001',
            'channel' => 'meeting',
            'content' => null,
            'tenant_id' => $tenant->id,
        ]);
    });

    it('returns empty list for visitor with no communications', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.communications.index', $visitor->vin));

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

        $response = $this->actingAs($user)->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'sms',
            'content' => 'Test SMS content.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'channel',
                'channel_label',
                'content',
                'type',
                'sent_at',
                'created_at',
            ],
        ]);
        $response->assertJson([
            'data' => [
                'channel' => 'sms',
                'channel_label' => 'SMS',
                'type' => 'system',
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

        $response = $this->post(route('visitors.communications.store', $visitor->vin), [
            'channel' => 'sms',
            'content' => 'Test SMS.',
        ]);

        $response->assertStatus(403);
    });
});
