<?php

use App\Models\KnowledgeItem;
use App\Models\KnowledgeSharing;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Knowledge Management', function () {
    it('can create a knowledge item', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->post(route('knowledge-items.store'), [
            'title' => 'Test Document',
            'description' => 'A test document',
            'link' => 'https://example.com/document',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('knowledge_items', [
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can list knowledge items', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        KnowledgeItem::create([
            'tenant_id' => $tenant->id,
            'knw' => 'KNW-2026-000001',
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'version' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('knowledge-items.index'));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('can get knowledge item detail', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $item = KnowledgeItem::create([
            'tenant_id' => $tenant->id,
            'knw' => 'KNW-2026-000001',
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'version' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('knowledge-items.show', $item->id));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'knw' => 'KNW-2026-000001',
                'title' => 'Test Document',
            ],
        ]);
    });

    it('can share a knowledge item with a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $item = KnowledgeItem::create([
            'tenant_id' => $tenant->id,
            'knw' => 'KNW-2026-000001',
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'version' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('knowledge-items.share', $item->id), [
            'vin' => 'VC-2026-000001',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('knowledge_sharings', [
            'knowledge_item_id' => $item->id,
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'granted',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('creates a Knowledge Shared timeline event when sharing', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $item = KnowledgeItem::create([
            'tenant_id' => $tenant->id,
            'knw' => 'KNW-2026-000001',
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'version' => 1,
        ]);

        $this->actingAs($user)->post(route('knowledge-items.share', $item->id), [
            'vin' => 'VC-2026-000001',
        ]);

        $this->assertDatabaseHas('timeline_events', [
            'visitor_vin' => 'VC-2026-000001',
            'type' => 'system',
            'source' => 'Knowledge Shared',
            'tenant_id' => $tenant->id,
        ]);
    });

    it('can revoke access to a knowledge item', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $item = KnowledgeItem::create([
            'tenant_id' => $tenant->id,
            'knw' => 'KNW-2026-000001',
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'version' => 1,
        ]);

        KnowledgeSharing::create([
            'knowledge_item_id' => $item->id,
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'granted',
        ]);

        $response = $this->actingAs($user)->delete(route('knowledge-items.revoke', [
            'itemId' => $item->id,
            'vin' => 'VC-2026-000001',
        ]));

        $response->assertStatus(200);
        $this->assertDatabaseHas('knowledge_sharings', [
            'knowledge_item_id' => $item->id,
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'revoked',
        ]);
    });

    it('can list knowledge items shared with a visitor', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);
        $visitor = Visitor::create([
            'tenant_id' => $tenant->id,
            'vin' => 'VC-2026-000001',
            'name' => 'Test Visitor',
            'lifecycle_state' => 'Interested',
        ]);

        $item = KnowledgeItem::create([
            'tenant_id' => $tenant->id,
            'knw' => 'KNW-2026-000001',
            'title' => 'Test Document',
            'link' => 'https://example.com/document',
            'version' => 1,
        ]);

        KnowledgeSharing::create([
            'knowledge_item_id' => $item->id,
            'tenant_id' => $tenant->id,
            'visitor_vin' => 'VC-2026-000001',
            'status' => 'granted',
        ]);

        $response = $this->actingAs($user)->get(route('visitors.knowledge.index', $visitor->vin));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    it('returns 404 for non-existent knowledge item', function () {
        $tenant = Tenant::create(['name' => 'Test Tenant']);
        $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get(route('knowledge-items.show', 99999));

        $response->assertStatus(404);
    });
});
