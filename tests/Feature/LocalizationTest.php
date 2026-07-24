<?php

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    App::setLocale('hu');
});

test('the auth failed message is translated to Hungarian', function (): void {
    expect(trans('auth.failed'))
        ->toBe('A megadott adatok nem egyeznek a nyilvántartásunkkal.')
        ->not->toBe('These credentials do not match our records.');
});

test('the auth throttle and password messages are translated to Hungarian', function (): void {
    expect(trans('auth.password'))->toBe('A megadott jelszó hibás.');
    expect(trans('auth.throttle', ['seconds' => 30]))
        ->toBe('Túl sok bejelentkezési kísérlet. Kérjük, próbálja újra 30 másodperc múlva.');
});

test('previously untranslated interface strings now resolve to Hungarian', function (string $key, string $expected): void {
    expect(__($key))->toBe($expected)->not->toBe($key);
})->with([
    ['Toggle dark mode', 'Sötét mód váltása'],
    ['Open user menu', 'Felhasználói menü megnyitása'],
    ['Open main menu', 'Főmenü megnyitása'],
    ['Reset password', 'Jelszó visszaállítása'],
    ['You do not have permission to perform this action.', 'Nincs jogosultsága a művelet végrehajtásához.'],
    ['Organization created successfully.', 'A szervezet sikeresen létrejött.'],
    ['Tool updated successfully.', 'Az eszköz sikeresen frissítve.'],
]);

test('no __() key used in the application is missing a Hungarian pair', function (): void {
    $translations = json_decode((string) file_get_contents(lang_path('hu.json')), true);

    $sources = collect()
        ->merge(File::allFiles(app_path()))
        ->merge(File::allFiles(resource_path('views')));

    $missing = [];
    foreach ($sources as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        preg_match_all("/__\('([^']+)'\)/", (string) file_get_contents($file->getRealPath()), $matches);
        foreach ($matches[1] as $key) {
            // Dotted keys resolve from PHP translation files, not the JSON catalogue.
            if (str_contains($key, '.') && preg_match('/^[a-z_]+\.[a-z_.]+$/', $key)) {
                continue;
            }

            if (! array_key_exists($key, $translations)) {
                $missing[$key] = $file->getRelativePathname();
            }
        }
    }

    expect($missing)->toBe([], 'Missing Hungarian translation pairs: ' . json_encode($missing, JSON_UNESCAPED_UNICODE));
});
