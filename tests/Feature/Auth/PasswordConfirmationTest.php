<?php

declare(strict_types=1);

use App\Livewire\Auth\ConfirmPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

test('confirm password screen can be rendered', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertStatus(200);
});

test('password can be confirmed', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    Livewire::test(ConfirmPassword::class)
        ->set('password', 'password')
        ->call('confirmPassword')
        ->assertHasNoErrors()
        ->assertRedirect();
});

test('password is not confirmed with invalid password', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    Livewire::test(ConfirmPassword::class)
        ->set('password', 'wrong-password')
        ->call('confirmPassword')
        ->assertHasErrors('password');
});
