<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

test('authenticated user can view profile edit page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('profile.edit'));

    $response->assertSuccessful();
});

test('authenticated user can update profile name and password', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
    ]);

    $response = $this->actingAs($user)->post(route('profile.update'), [
        'name' => 'New Name',
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertRedirect(route('home'));
    $user->refresh();

    expect($user->name)->toBe('New Name');
    expect(Hash::check('newpassword123', $user->password))->toBeTrue();
});

test('authenticated user can upload a custom avatar', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('avatar.jpg');

    $response = $this->actingAs($user)->post(route('profile.update'), [
        'name' => 'Test User',
        'avatar' => $file,
    ]);

    $response->assertRedirect(route('home'));
    $user->refresh();

    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
});

test('default avatar fallback matches expected stock animal', function () {
    // Override ID sequence using factory
    $user = User::factory()->create(['id' => 1]); // 1 % 5 = 1 => 'fox'
    expect($user->avatar_url)->toContain('images/avatars/fox.svg');

    $user2 = User::factory()->create(['id' => 2]); // 2 % 5 = 2 => 'koala'
    expect($user2->avatar_url)->toContain('images/avatars/koala.svg');
});
