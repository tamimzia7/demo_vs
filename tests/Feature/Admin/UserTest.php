<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Management', function () {
    it('can display users list', function () {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Users');
    });

    it('can create a user', function () {
        $user = User::factory()->create(['role' => 'super_admin']);
        $tenant = Tenant::create(['name' => 'Test Tenant']);

        $response = $this->actingAs($user)->post(route('admin.users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'company_owner',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'company_owner',
        ]);
    });

    it('can update a user', function () {
        $user = User::factory()->create(['role' => 'super_admin']);
        $targetUser = User::factory()->create(['role' => 'company_owner']);

        $response = $this->actingAs($user)->put(route('admin.users.update', $targetUser->id), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'role' => 'sales_executive',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Updated Name',
            'role' => 'sales_executive',
        ]);
    });
});
