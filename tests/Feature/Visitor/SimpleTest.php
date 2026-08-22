<?php

use App\Models\Tenant;
use App\Models\User;

it('visitor index route exists', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant']);
    $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->get('/visitors');
    $response->assertStatus(200);
});
