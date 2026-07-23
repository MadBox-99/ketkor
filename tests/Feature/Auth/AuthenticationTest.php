<?php

declare(strict_types=1);

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\assertGuest;

use Tests\TestCase;

test('login screen can be rendered', function (): void {
    /** @var TestCase $this */
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function (): void {
    $user = User::factory()->createOne([
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'password')
        ->call('login')
        ->assertRedirect();

    expect(auth()->check())->toBeTrue();
    expect(auth()->user()->id)->toBe($user->id);
});

test('users can not authenticate with invalid password', function (): void {
    $user = User::factory()->createOne([
        'password' => Hash::make('password'),
    ]);

    Livewire::test(Login::class)
        ->set('email', $user->email)
        ->set('password', 'wrong-password')
        ->call('login')
        ->assertHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

test('users can logout', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->createOne();

    $response = $this->actingAs($user)->post('/logout');

    assertGuest();
    $response->assertRedirect(route('login'));
});
