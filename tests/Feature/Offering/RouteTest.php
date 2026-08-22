<?php

use App\Models\Tenant;
use App\Models\User;

it('offerings index route exists', function () {
    $tenant = Tenant::create(['name' => 'Test Tenant']);
    $user = User::factory()->create(['role' => 'super_admin', 'tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)->get('/offerings');
    $response->assertStatus(200);
});
