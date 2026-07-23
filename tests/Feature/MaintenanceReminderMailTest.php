<?php

declare(strict_types=1);

use App\Mail\MaintenanceReminderMail;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;

function reminderMail(?string $bookingUrl = null): MaintenanceReminderMail
{
    return new MaintenanceReminderMail(
        product: Product::factory()->createOne(['serial_number' => 'AB-1234-CDEF']),
        subjectLine: 'Esedékes karbantartás - AB-1234-CDEF',
        body: "Tisztelt Kiss Béla!\nA karbantartás 2026. 03. 10. napján esedékes.",
        bookingUrl: $bookingUrl,
        contactPhone: '+36 1 234 5678',
        contactEmail: 'szerviz@example.test',
    );
}

it('uses the rendered subject line', function (): void {
    expect(reminderMail()->envelope()->subject)->toBe('Esedékes karbantartás - AB-1234-CDEF');
});

it('renders the body and the contact details', function (): void {
    $rendered = reminderMail()->render();

    expect($rendered)->toContain('Tisztelt Kiss Béla!')
        ->and($rendered)->toContain('2026. 03. 10.')
        ->and($rendered)->toContain('+36 1 234 5678')
        ->and($rendered)->toContain('szerviz@example.test');
});

it('renders a booking button only when a booking url is set', function (): void {
    expect(reminderMail('https://example.test/foglalas')->render())
        ->toContain('https://example.test/foglalas')
        ->and(reminderMail()->render())->not->toContain('Időpont foglalása');
});

it('is queueable', function (): void {
    expect(reminderMail())->toBeInstanceOf(ShouldQueue::class);
});

it('does not turn markdown in the body into links', function (): void {
    $mail = new MaintenanceReminderMail(
        product: Product::factory()->createOne(['serial_number' => 'AB-1234-CDEF']),
        subjectLine: 'Esedékes karbantartás',
        body: 'Tisztelt [Kattints ide](https://evil.example)!',
        bookingUrl: null,
        contactPhone: null,
        contactEmail: null,
    );

    $rendered = $mail->render();

    expect($rendered)->not->toContain('href="https://evil.example"')
        ->and($rendered)->toContain('[Kattints ide](https://evil.example)');
});
