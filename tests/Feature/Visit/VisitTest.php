<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;
use App\Models\VisitParticipant;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Visit Management', function () {
    it('can log a visit with participants', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->post(route('visitors.visits.store', $visitor->vin), [
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
            'outcome' => 'Discussed pricing',
            'participants' => ['John Doe', 'Jane Smith'],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('visits', [
            'visitor_vin' => 'VC-2026-000001',
            'visit_date' => '2026-08-22 00:00:00',
            'context' => 'Office meeting',
            'outcome' => 'Discussed pricing',
            'tenant_id' => $tenant->id,
        ]);
        $this->assertDatabaseHas('visit_participants', [
            'name' => 'John Doe',
            'tenant_id' => $tenant->id,
        ]);
        $this->assertDatabaseHas('visit_participants', [
            'name' => 'Jane Smith',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a timeline event when logging a visit', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $this->actingAs($user)->post(route('visitors.visits.store', $visitor->vin), [
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
            'outcome' => 'Discussed pricing',
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'user',
            'source' => 'Visit',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can list visits for a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        Visit::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
            'outcome' => 'Discussed pricing',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.visits.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('can get visit detail', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $visit = Visit::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
            'outcome' => 'Discussed pricing',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.visits.show', [
            'vin' => $visitor->vin,
            'visitId' => $visit->id,
        ]));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'context' => 'Office meeting',
                'outcome' => 'Discussed pricing',
            ],
        ]);
    });

    it('can promote a participant to a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $visit = Visit::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
        ]);

        $participant = VisitParticipant::create([
            'visit_id' => $visit->id,
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
        ]);

        $response = $this->actingAs($user)->post(route('participants.promote', $participant->id));

        $response->assertStatus(201);
        $this->assertDatabaseHas('visitors', [
            'name' => 'John Doe',
            'tenant_id' => $tenant->id,
        ]);
        $this->assertDatabaseHas('visit_participants', [
            'id' => $participant->id,
            'promoted_to_vin' => 'VC-2026-000002',
        ]);
    });

    it('creates a system event when promoting a participant', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $visit = Visit::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
        ]);

        $participant = VisitParticipant::create([
            'visit_id' => $visit->id,
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
        ]);

        $this->actingAs($user)->post(route('participants.promote', $participant->id));

        $this->assertDatabaseHas('timeline_events', [
            'type' => 'system',
            'source' => 'Visitor Created',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('cannot promote an already promoted participant', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $visit = Visit::create([
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'visit_date' => '2026-08-22',
            'context' => 'Office meeting',
        ]);

        $participant = VisitParticipant::create([
            'visit_id' => $visit->id,
            'tenant_id' => $tenant->id,
            'name' => 'John Doe',
            'promoted_to_vin' => 'VC-2026-000002',
        ]);

        $response = $this->actingAs($user)->post(route('participants.promote', $participant->id));

        $response->assertStatus(422);
    });

    it('returns 404 for non-existent visit', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.visits.show', [
            'vin' => $visitor->vin,
            'visitId' => 99999,
        ]));

        $response->assertStatus(404);
    });
});
