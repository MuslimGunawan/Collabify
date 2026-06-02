<?php

use App\Models\User;

test('redirects guest users to login', function () {
    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('allows logged in users to access dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk();
});