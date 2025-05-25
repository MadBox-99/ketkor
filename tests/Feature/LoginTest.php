<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Login (Livewire)', function (): void {
    it('allows user to login with correct credentials', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('login')
            ->assertRedirect('/'); // vagy a megfelelő átirányítás

        $this->assertAuthenticatedAs($user);
    });

    it('does not allow user to login with invalid credentials', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        Livewire::test('auth.login')
            ->set('email', $user->email)
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertHasErrors('email');

        $this->assertGuest();
    });
});
