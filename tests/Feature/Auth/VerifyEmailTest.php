<?php

declare(strict_types=1);

use App\Livewire\Auth\VerifyEmail;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('sends verification email to unverified user', function (): void {
    Notification::fake();

    $user = User::factory()->unverified()->create();
    actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification');

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('redirects already verified user instead of sending email', function (): void {
    Notification::fake();

    $user = User::factory()->create(); // verified by default
    actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('sendVerification')
        ->assertRedirect();

    Notification::assertNotSentTo($user, VerifyEmailNotification::class);
});

it('can logout from verify email page', function (): void {
    $user = User::factory()->unverified()->create();
    actingAs($user);

    Livewire::test(VerifyEmail::class)
        ->call('logout')
        ->assertRedirect('/');
});
