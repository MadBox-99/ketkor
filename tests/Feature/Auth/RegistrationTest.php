<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Auth\Register;
use App\Models\User;
use App\Notifications\AdminUserRegistered;
use Illuminate\Support\Facades\Notification;
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
        ->assertRedirect(route('verification.notice'));

    $this->assertAuthenticated();
});

test('new users get organizer role by default', function (): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('register');

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user->hasRole(UserRole::Organizer))->toBeTrue();
});

test('new users must verify email', function (): void {
    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('register');

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user->hasVerifiedEmail())->toBeFalse();
});

test('admins are notified when a new user registers', function (): void {
    Notification::fake();

    config()->set('app.admin_emails', [
        'hegedus.csaba@ketkorkft.hu',
        'denes.katalin@ketkorkft.hu',
        'szolnoki.peter@ketkorkft.hu',
    ]);

    Livewire::test(Register::class)
        ->fillForm([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->call('register');

    Notification::assertSentOnDemand(
        AdminUserRegistered::class,
        function (AdminUserRegistered $notification, array $channels, object $notifiable): bool {
            return $notifiable->routes['mail'] === [
                'hegedus.csaba@ketkorkft.hu',
                'denes.katalin@ketkorkft.hu',
                'szolnoki.peter@ketkorkft.hu',
            ];
        },
    );
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
