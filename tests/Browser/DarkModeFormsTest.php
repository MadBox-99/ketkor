<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;

use Spatie\Permission\Models\Role;

/**
 * The layout paints a dark background via dark:bg-gray-900, so every form control
 * has to carry its own dark variants. These tests measure the rendered colours
 * rather than inspecting classes, so they stay valid whichever component library
 * the forms end up using.
 */
beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }

    $organization = Organization::factory()->createOne();
    $user = User::factory()->createOne(['organization_id' => $organization->id]);
    $user->assignRole(UserRole::Organizer);

    actingAs($user);
});

it('renders the organization form legibly in dark mode', function (): void {
    $page = visit(route('organizations.myorganization'));

    $page->assertNoJavascriptErrors()
        // Switch to dark mode the same way a user would.
        ->click('#theme-toggle')
        ->assertScript('document.documentElement.classList.contains("dark")', true)
        // A label that stays near-black on the dark page is unreadable. Assert the
        // text is light, i.e. its red channel is high.
        ->assertScript(<<<'JS'
            (() => {
                const label = document.querySelector('label[for="form.name"]');
                if (! label) { return 'no-label'; }
                const rgb = getComputedStyle(label).color.match(/\d+/g).map(Number);
                return rgb[0] > 140 ? 'light' : 'dark';
            })()
        JS, 'light')
        // A light label only helps if the surface behind it is dark too. Walk up
        // from the label and find the first ancestor that actually paints a
        // background, then assert it is not a light surface.
        ->assertScript(<<<'JS'
            (() => {
                let node = document.querySelector('label[for="form.name"]');
                if (! node) { return 'no-label'; }
                while (node && node !== document.documentElement) {
                    const bg = getComputedStyle(node).backgroundColor;
                    const rgba = bg.match(/[\d.]+/g).map(Number);
                    const opaque = rgba.length < 4 || rgba[3] > 0;
                    if (opaque && bg !== 'rgba(0, 0, 0, 0)') {
                        return rgba[0] > 140 ? 'light' : 'dark';
                    }
                    node = node.parentElement;
                }
                return 'none';
            })()
        JS, 'dark');
});
