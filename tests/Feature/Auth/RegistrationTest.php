<?php

declare(strict_types=1);

use App\Livewire\Auth\Register;
use App\Models\User;
use Livewire\Livewire;

test('registration screen can be rendered', function (): void {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertRedirect();

    $this->assertAuthenticated();
});

test('registration requires valid email', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'not-an-email')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasErrors(['email']);
});

test('registration requires password confirmation', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'wrong-password')
        ->call('register')
        ->assertHasErrors(['password']);
});

test('registration requires unique email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Register::class)
        ->set('name', 'Test User')
        ->set('email', 'taken@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->call('register')
        ->assertHasErrors(['email']);
});
