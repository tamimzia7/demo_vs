<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Authentication', function () {
    it('shows the login page to guests', function () {
        $this->get(route('login'))->assertOk();
    });

    it('redirects an unauthenticated user away from a protected route', function () {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    });

    it('authenticates a valid user and redirects to the dashboard', function () {
        $tenant = Tenant::create(['name' => 'Acme Co']);
        $user = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => 'secret123',
            'role' => 'company_owner',
            'tenant_id' => $tenant->id,
        ]);

        $this->post(route('login'), [
            'email' => 'owner@example.com',
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    });

    it('rejects invalid credentials without authenticating', function () {
        Tenant::create(['name' => 'Acme Co']);
        User::factory()->create([
            'email' => 'owner@example.com',
            'password' => 'secret123',
            'role' => 'company_owner',
            'tenant_id' => Tenant::first()->id,
        ]);

        $this->post(route('login'), [
            'email' => 'owner@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    });

    it('logs an authenticated user out', function () {
        $user = User::factory()->create(['role' => 'company_owner']);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/');

        $this->assertGuest();
    });
});
