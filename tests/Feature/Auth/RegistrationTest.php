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
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('register')
        ->assertRedirect();

    $this->assertAuthenticated();
});

test('registration requires valid email', function (): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('register')
        ->assertHasFormErrors(['email']);
});

test('registration requires password confirmation', function (): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-password',
        ])
        ->call('register')
        ->assertHasFormErrors(['password']);
});

test('registration requires unique email', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('register')
        ->assertHasFormErrors(['email']);
});
