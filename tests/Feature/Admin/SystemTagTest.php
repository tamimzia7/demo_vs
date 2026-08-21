<?php

use App\Models\SystemTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('System Tag Management', function () {
    it('can display system tags list', function () {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->get(route('admin.system-tags.index'));

        $response->assertStatus(200);
        $response->assertSee('System Tags');
    });

    it('can create a system tag', function () {
        $user = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($user)->post(route('admin.system-tags.store'), [
            'name' => 'Test Tag',
            'color' => '#ff0000',
            'description' => 'A test tag',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('system_tags', [
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ]);
    });

    it('cannot delete a system tag', function () {
        $user = User::factory()->create(['role' => 'super_admin']);
        $tag = SystemTag::create(['name' => 'Test Tag', 'slug' => 'test-tag']);

        $response = $this->actingAs($user)->delete(route('admin.system-tags.destroy', $tag->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('system_tags', ['id' => $tag->id]);
    });
});
