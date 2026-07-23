<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;

/**
 * These tests pin the two pieces of hand-written JavaScript in resources/js/app.js:
 * the dark mode toggle and the mobile navigation menu. Both survive Livewire's
 * wire:navigate DOM swaps, which is the part that is easy to break.
 */
beforeEach(function (): void {
    actingAs(User::factory()->createOne());
});

it('has no javascript errors on the main pages', function (): void {
    $page = visit('/');

    $page->assertNoJavascriptErrors();
});

it('toggles dark mode and remembers the choice', function (): void {
    $page = visit('/');

    $page->assertNoJavascriptErrors()
        ->click('#theme-toggle')
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->assertScript('localStorage.getItem("color-theme")', 'dark')
        ->click('#theme-toggle')
        ->assertScript('document.documentElement.classList.contains("dark")', false)
        ->assertScript('localStorage.getItem("color-theme")', 'light');
});

it('keeps the dark mode toggle working after a wire:navigate visit', function (): void {
    // This is the case the hand-rolled re-initialisation in app.js exists for:
    // wire:navigate swaps the DOM without re-running the bundle.
    $page = visit('/');

    $page->assertNoJavascriptErrors()
        ->click('#theme-toggle')
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        ->navigate(route('profile.edit'))
        ->assertNoJavascriptErrors()
        ->click('#theme-toggle')
        ->assertScript('document.documentElement.classList.contains("dark")', false);
});

it('opens and closes the mobile menu', function (): void {
    // The toggle sits in a md:hidden wrapper, so it is only clickable below 768px.
    $page = visit('/')->on()->mobile();

    $page->assertNoJavascriptErrors()
        ->assertScript('document.getElementById("mobile-menu").classList.contains("hidden")', true)
        ->click('#menu-toggle')
        ->assertScript('document.getElementById("mobile-menu").classList.contains("hidden")', false)
        ->click('#menu-toggle')
        ->assertScript('document.getElementById("mobile-menu").classList.contains("hidden")', true);
});
