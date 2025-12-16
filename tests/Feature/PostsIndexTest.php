<?php

use App\Models\Post;

beforeEach(function () {
    $this->user = \App\Models\User::factory()->create();
});

it('shows posts on index page', function () {
    Post::factory()->count(3)->create();

    $this->actingAs($this->user)
        ->get('/posts')
        ->assertStatus(200)
        ->assertSee('Posts')
        ->assertSee(Post::first()->title);
});
