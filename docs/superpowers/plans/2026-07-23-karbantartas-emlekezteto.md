# Karbantartás emlékeztető e-mail rendszer — implementációs terv

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A rendszer naponta egyszer automatikusan e-mail emlékeztetőt küld az ügyfeleknek a gázkészülékük következő karbantartásának esedékessége előtt, illetve ismétlő figyelmeztetést, ha az esedékes karbantartás elmaradt.

**Architecture:** Az esedékesség menet közben számítódik a munkalapokból (`product_logs`, `what = maintenance`), fallback a `products.installation_date`. Egy napi Artisan command végigmegy a készülék × címzett párokon, eldönti, hogy az adott napra esik-e emlékeztető szakasz, és kiküldi a levelet. A `maintenance_reminders` napló unique indexe garantálja, hogy egy szakasz egy esedékességre és egy címzettre egyszer menjen ki. Nincs denormalizált esedékesség-oszlop, amit szinkronban kellene tartani.

**Tech Stack:** PHP 8.4, Laravel 13, Filament v5, Pest v4, Livewire v4, MySQL/MariaDB (Herd).

**Spec:** `docs/superpowers/specs/2026-07-23-karbantartas-emlekezteto-design.md`

## Global Constraints

- Minden PHP fájl `declare(strict_types=1);` deklarációval kezdődik, a meglévő fájlok mintájára.
- Modellek fillable listája a projekt konvenciója szerint `#[Fillable([...])]` attribútummal, nem `$fillable` property-vel.
- Casts a `#[Override] protected function casts(): array` metódusban.
- Explicit return type és paraméter típus minden metóduson; PHPDoc array shape ott, ahol tömböt adunk vissza.
- Vezérlési szerkezetek mindig kapcsos zárójellel.
- Filament névterek: form mezők `Filament\Forms\Components\`, layout `Filament\Schemas\Components\`, táblaoszlopok `Filament\Tables\Columns\`, akciók `Filament\Actions\` (soha nem `Filament\Tables\Actions\`), ikonok `Filament\Support\Icons\Heroicon`.
- Minden feladat végén: `vendor/bin/pint --dirty --format agent`, majd a feladat tesztjeinek futtatása, csak utána commit.
- A tesztek `tests/Pest.php`-ban be van fagyasztva az idő (`freezeTime()`), ezért a dátumfüggő teszteknél `travelTo()` használandó, `Carbon::setTestNow()` helyett.
- Új Artisan / Filament fájlok generálása mindig `--no-interaction` kapcsolóval.
- Felhasználónak látszó szövegek magyarul, a meglévő Filament erőforrások és a `resources/views/emails/worksheet.blade.php` stílusában.

## Fájlstruktúra

**Létrehozandó:**

| Fájl | Felelősség |
| --- | --- |
| `app/Enums/MaintenanceReminderStage.php` | Emlékeztető szakasz: előzetes / lejárt / manuális |
| `app/Enums/MaintenanceReminderStatus.php` | Kiküldés eredménye: sent / failed |
| `app/Models/MaintenanceReminderSetting.php` | Egysoros globális beállítás, `current()` accessorral |
| `app/Models/MaintenanceReminder.php` | Kiküldési napló rekord |
| `app/Support/MaintenanceSchedule.php` | Esedékesség számítása egy készülékre (alapdátum + intervallum) |
| `app/Support/PendingMaintenanceReminder.php` | Egy konkrét kiküldendő emlékeztető adatai (DTO) |
| `app/Support/MaintenanceReminderTemplateRenderer.php` | A sablon változóinak feloldása |
| `app/Services/MaintenanceReminderScheduler.php` | Jogosultsági szűrő, szakasz-döntés, küldés és naplózás |
| `app/Mail/MaintenanceReminderMail.php` | A kimenő levél |
| `resources/views/emails/maintenance-reminder.blade.php` | A levél sablonja |
| `app/Console/Commands/SendMaintenanceRemindersCommand.php` | Napi CLI belépési pont |
| `routes/console.php` | Az ütemezés regisztrálása |
| `app/Filament/Resources/MaintenanceReminders/**` | Csak olvasható napló lista |
| `app/Filament/Pages/MaintenanceReminderSettingsPage.php` | Beállítás- és sablonszerkesztő oldal |
| `resources/views/filament/pages/maintenance-reminder-settings.blade.php` | A beállítás oldal nézete |

**Módosítandó:** `app/Models/Product.php`, `app/Models/User.php`, `database/factories/ProductLogFactory.php`, `app/Filament/Resources/Products/Schemas/ProductFormSchema.php`, `app/Filament/Resources/Products/Tables/ProductTable.php`, `app/Filament/Resources/Users/Schemas/UserFormSchema.php`.

---

### Task 1: Globális beállítás modell

**Files:**
- Create: `database/migrations/<generált>_create_maintenance_reminder_settings_table.php`
- Create: `app/Models/MaintenanceReminderSetting.php`
- Test: `tests/Feature/MaintenanceReminderSettingTest.php`

**Interfaces:**
- Produces: `MaintenanceReminderSetting::current(): MaintenanceReminderSetting` — mindig ugyanazt az egyetlen rekordot adja vissza, szükség esetén létrehozza az alapértelmezésekkel. Attribútumok: `enabled` (bool), `advance_days` (int lista), `overdue_repeat_days` (int), `overdue_max_count` (int), `contact_phone`, `contact_email`, `booking_url` (mind `?string`), `email_subject` (string), `email_body` (string).

- [ ] **Step 1: Írd meg a bukó tesztet**

Hozd létre a `tests/Feature/MaintenanceReminderSettingTest.php` fájlt:

```php
<?php

declare(strict_types=1);

use App\Models\MaintenanceReminderSetting;

it('creates the settings row with defaults on first access', function (): void {
    $settings = MaintenanceReminderSetting::current();

    expect($settings->exists)->toBeTrue()
        ->and($settings->enabled)->toBeTrue()
        ->and($settings->advance_days)->toBe([30, 7])
        ->and($settings->overdue_repeat_days)->toBe(14)
        ->and($settings->overdue_max_count)->toBe(3)
        ->and($settings->email_subject)->not->toBeEmpty()
        ->and($settings->email_body)->toContain('{{ serial_number }}');
});

it('returns the same row on repeated access', function (): void {
    $first = MaintenanceReminderSetting::current();
    $second = MaintenanceReminderSetting::current();

    expect($second->getKey())->toBe($first->getKey())
        ->and(MaintenanceReminderSetting::query()->count())->toBe(1);
});

it('persists edited settings', function (): void {
    MaintenanceReminderSetting::current()->update([
        'advance_days' => [45, 14],
        'contact_phone' => '+36 1 234 5678',
    ]);

    $settings = MaintenanceReminderSetting::current();

    expect($settings->advance_days)->toBe([45, 14])
        ->and($settings->contact_phone)->toBe('+36 1 234 5678');
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderSettingTest`
Expected: FAIL — `Class "App\Models\MaintenanceReminderSetting" not found`

- [ ] **Step 3: Generáld és töltsd ki a migrációt**

Run: `php artisan make:migration create_maintenance_reminder_settings_table --no-interaction`

A generált fájl `up()` metódusa:

```php
Schema::create('maintenance_reminder_settings', function (Blueprint $table): void {
    $table->id();
    $table->boolean('enabled')->default(true);
    $table->json('advance_days');
    $table->unsignedSmallInteger('overdue_repeat_days')->default(14);
    $table->unsignedTinyInteger('overdue_max_count')->default(3);
    $table->string('contact_phone', 100)->nullable();
    $table->string('contact_email', 255)->nullable();
    $table->string('booking_url', 500)->nullable();
    $table->string('email_subject', 255);
    $table->text('email_body');
    $table->timestamps();
});
```

A `down()` metódus: `Schema::dropIfExists('maintenance_reminder_settings');`

- [ ] **Step 4: Írd meg a modellt**

`app/Models/MaintenanceReminderSetting.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'enabled',
    'advance_days',
    'overdue_repeat_days',
    'overdue_max_count',
    'contact_phone',
    'contact_email',
    'booking_url',
    'email_subject',
    'email_body',
])]
class MaintenanceReminderSetting extends Model
{
    public const DEFAULT_SUBJECT = 'Esedékes karbantartás - {{ serial_number }}';

    public const DEFAULT_BODY = <<<'TEXT'
        Tisztelt {{ owner_name }}!

        Ezúton értesítjük, hogy a(z) {{ tool_name }} készülékének ({{ serial_number }}) {{ maintenance_type }} karbantartása {{ due_date }} napján esedékes.

        Előző karbantartás: {{ last_maintenance_date }}

        Kérjük, egyeztessen velünk időpontot:
        Telefon: {{ contact_phone }}
        E-mail: {{ contact_email }}

        Köszönjük a bizalmát!
        TEXT;

    /**
     * A rendszer egyetlen beállítás rekordja, szükség esetén létrehozva.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'enabled' => true,
            'advance_days' => [30, 7],
            'overdue_repeat_days' => 14,
            'overdue_max_count' => 3,
            'email_subject' => self::DEFAULT_SUBJECT,
            'email_body' => self::DEFAULT_BODY,
        ]);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'advance_days' => 'array',
            'overdue_repeat_days' => 'integer',
            'overdue_max_count' => 'integer',
        ];
    }
}
```

- [ ] **Step 5: Futtasd a tesztet**

Run: `php artisan test --compact --filter=MaintenanceReminderSettingTest`
Expected: PASS (3 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/MaintenanceReminderSetting.php database/migrations tests/Feature/MaintenanceReminderSettingTest.php
git commit -m "feat: add maintenance reminder settings model"
```

---

### Task 2: Készülék és felhasználó oszlopok, factory-k

**Files:**
- Create: `database/migrations/<generált>_add_maintenance_reminder_columns_to_products_and_users_tables.php`
- Modify: `app/Models/Product.php` (`#[Fillable]` lista, `casts()`)
- Modify: `app/Models/User.php` (`#[Fillable]` lista, `casts()`)
- Modify: `database/factories/ProductLogFactory.php`
- Test: `tests/Feature/MaintenanceReminderColumnsTest.php`

**Interfaces:**
- Consumes: semmi korábbi feladatból.
- Produces: `Product->maintenance_interval_months` (int, default 12), `Product->maintenance_reminders_enabled` (bool, default true), `User->maintenance_reminders_enabled` (bool, default true). `ProductLogFactory` alapállapota `what = 'maintenance'`, és van `installation()` state.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceReminderColumnsTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\ProductLogType;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;

it('defaults products to yearly maintenance with reminders enabled', function (): void {
    $product = Product::factory()->createOne();

    expect($product->maintenance_interval_months)->toBe(12)
        ->and($product->maintenance_reminders_enabled)->toBeTrue();
});

it('allows a half-yearly interval and disabling reminders per product', function (): void {
    $product = Product::factory()->createOne([
        'maintenance_interval_months' => 6,
        'maintenance_reminders_enabled' => false,
    ]);

    expect($product->fresh()->maintenance_interval_months)->toBe(6)
        ->and($product->fresh()->maintenance_reminders_enabled)->toBeFalse();
});

it('defaults users to reminders enabled', function (): void {
    expect(User::factory()->createOne()->maintenance_reminders_enabled)->toBeTrue();
});

it('creates maintenance product logs by default', function (): void {
    $log = ProductLog::factory()->createOne();

    expect($log->what)->toBe(ProductLogType::Maintenance)
        ->and($log->when)->not->toBeNull();
});

it('creates installation product logs via the state', function (): void {
    expect(ProductLog::factory()->installation()->createOne()->what)
        ->toBe(ProductLogType::Installation);
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderColumnsTest`
Expected: FAIL — `Undefined property` / `Column not found: maintenance_interval_months`

- [ ] **Step 3: Generáld és töltsd ki a migrációt**

Run: `php artisan make:migration add_maintenance_reminder_columns_to_products_and_users_tables --no-interaction`

```php
public function up(): void
{
    Schema::table('products', function (Blueprint $table): void {
        $table->unsignedTinyInteger('maintenance_interval_months')->default(12);
        $table->boolean('maintenance_reminders_enabled')->default(true);
    });

    Schema::table('users', function (Blueprint $table): void {
        $table->boolean('maintenance_reminders_enabled')->default(true);
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table): void {
        $table->dropColumn(['maintenance_interval_months', 'maintenance_reminders_enabled']);
    });

    Schema::table('users', function (Blueprint $table): void {
        $table->dropColumn('maintenance_reminders_enabled');
    });
}
```

- [ ] **Step 4: Bővítsd a modelleket**

`app/Models/Product.php` — a `#[Fillable([...])]` lista végére, a `'created_at',` elé:

```php
    'maintenance_interval_months',
    'maintenance_reminders_enabled',
```

és a `casts()` visszatérési tömbjébe:

```php
'maintenance_interval_months' => 'integer',
'maintenance_reminders_enabled' => 'boolean',
```

`app/Models/User.php` — a `#[Fillable([...])]` lista végére:

```php
    'maintenance_reminders_enabled',
```

és a `casts()` tömbjébe:

```php
'maintenance_reminders_enabled' => 'boolean',
```

- [ ] **Step 5: Töltsd ki a ProductLogFactory-t**

`database/factories/ProductLogFactory.php` — a `definition()` metódus és két state:

```php
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'what' => ProductLogType::Maintenance,
            'comment' => fake()->optional(0.3)->sentence(),
            'when' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * A munkalap beüzemelést rögzít, nem karbantartást.
     */
    public function installation(): static
    {
        return $this->state(fn (): array => [
            'what' => ProductLogType::Installation,
        ]);
    }

    /**
     * A munkalap egy adott napon készült.
     */
    public function on(string $date): static
    {
        return $this->state(fn (): array => [
            'when' => $date,
        ]);
    }
```

A fájl tetején az importok közé: `use App\Enums\ProductLogType;` és `use App\Models\Product;`.

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderColumnsTest`
Expected: PASS (5 teszt)

- [ ] **Step 7: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/Product.php app/Models/User.php database/migrations database/factories/ProductLogFactory.php tests/Feature/MaintenanceReminderColumnsTest.php
git commit -m "feat: add maintenance reminder columns to products and users"
```

---

### Task 3: Esedékesség számítása

**Files:**
- Create: `app/Support/MaintenanceSchedule.php`
- Modify: `app/Models/Product.php` (`lastMaintenanceLog()` reláció, `nextMaintenanceDueDate()`)
- Test: `tests/Feature/MaintenanceScheduleTest.php`

**Interfaces:**
- Consumes: Task 2 `maintenance_interval_months` oszlopa, a `ProductLogFactory` `installation()` és `on()` state-jei.
- Produces:
  - `MaintenanceSchedule::for(Product $product): ?MaintenanceSchedule` — `null`, ha se karbantartási munkalap, se `installation_date` nincs.
  - `MaintenanceSchedule` readonly property-k: `public CarbonImmutable $dueDate`, `public CarbonImmutable $baseDate`, `public bool $fromMaintenanceLog`.
  - `Product::nextMaintenanceDueDate(): ?CarbonImmutable`
  - `Product::lastMaintenanceLog(): HasOne`

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceScheduleTest.php`:

```php
<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductLog;
use App\Support\MaintenanceSchedule;
use Carbon\CarbonImmutable;

it('counts twelve months from the last maintenance log', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2024-01-01']);
    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);

    $schedule = MaintenanceSchedule::for($product->fresh());

    expect($schedule->dueDate->toDateString())->toBe('2026-03-10')
        ->and($schedule->fromMaintenanceLog)->toBeTrue();
});

it('uses the latest maintenance log when several exist', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2024-01-01']);
    ProductLog::factory()->on('2024-06-01 09:00:00')->createOne(['product_id' => $product->id]);
    ProductLog::factory()->on('2025-08-20 09:00:00')->createOne(['product_id' => $product->id]);

    expect(MaintenanceSchedule::for($product->fresh())->dueDate->toDateString())
        ->toBe('2026-08-20');
});

it('ignores non-maintenance logs', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2024-01-01']);
    ProductLog::factory()->installation()->on('2025-09-09 09:00:00')
        ->createOne(['product_id' => $product->id]);

    $schedule = MaintenanceSchedule::for($product->fresh());

    expect($schedule->dueDate->toDateString())->toBe('2025-01-01')
        ->and($schedule->fromMaintenanceLog)->toBeFalse();
});

it('falls back to the installation date when there is no maintenance log', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2025-02-14']);

    expect(MaintenanceSchedule::for($product)->dueDate->toDateString())->toBe('2026-02-14');
});

it('honours the half-yearly interval', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => '2025-02-14',
        'maintenance_interval_months' => 6,
    ]);

    expect(MaintenanceSchedule::for($product)->dueDate->toDateString())->toBe('2025-08-14');
});

it('returns null when neither a maintenance log nor an installation date exists', function (): void {
    $product = Product::factory()->createOne(['installation_date' => null]);

    expect(MaintenanceSchedule::for($product))->toBeNull()
        ->and($product->nextMaintenanceDueDate())->toBeNull();
});

it('exposes the due date through the product', function (): void {
    $product = Product::factory()->createOne(['installation_date' => '2025-02-14']);

    expect($product->nextMaintenanceDueDate())->toBeInstanceOf(CarbonImmutable::class)
        ->and($product->nextMaintenanceDueDate()->toDateString())->toBe('2026-02-14');
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceScheduleTest`
Expected: FAIL — `Class "App\Support\MaintenanceSchedule" not found`

- [ ] **Step 3: Írd meg a MaintenanceSchedule osztályt**

`app/Support/MaintenanceSchedule.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Product;
use Carbon\CarbonImmutable;

final readonly class MaintenanceSchedule
{
    public function __construct(
        public CarbonImmutable $baseDate,
        public CarbonImmutable $dueDate,
        public bool $fromMaintenanceLog,
    ) {}

    /**
     * A készülék következő karbantartásának esedékessége, vagy null, ha nincs mihez viszonyítani.
     */
    public static function for(Product $product): ?self
    {
        $log = $product->lastMaintenanceLog;
        $base = $log?->when ?? $product->installation_date;

        if ($base === null) {
            return null;
        }

        $baseDate = CarbonImmutable::parse($base)->startOfDay();

        return new self(
            baseDate: $baseDate,
            dueDate: $baseDate->addMonths($product->maintenance_interval_months),
            fromMaintenanceLog: $log !== null,
        );
    }
}
```

- [ ] **Step 4: Bővítsd a Product modellt**

`app/Models/Product.php` — az importok közé:

```php
use App\Enums\ProductLogType;
use App\Support\MaintenanceSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\HasOne;
```

és a `product_logs()` metódus alá:

```php
    /**
     * A készülék legutolsó karbantartási munkalapja.
     */
    public function lastMaintenanceLog(): HasOne
    {
        return $this->hasOne(ProductLog::class)
            ->where('what', ProductLogType::Maintenance)
            ->latestOfMany('when');
    }

    /**
     * A következő karbantartás esedékessége, vagy null, ha nem számítható.
     */
    public function nextMaintenanceDueDate(): ?CarbonImmutable
    {
        return MaintenanceSchedule::for($this)?->dueDate;
    }
```

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceScheduleTest`
Expected: PASS (7 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Support/MaintenanceSchedule.php app/Models/Product.php tests/Feature/MaintenanceScheduleTest.php
git commit -m "feat: compute next maintenance due date from work sheets"
```

---

### Task 4: Emlékeztető napló modell

**Files:**
- Create: `app/Enums/MaintenanceReminderStage.php`
- Create: `app/Enums/MaintenanceReminderStatus.php`
- Create: `database/migrations/<generált>_create_maintenance_reminders_table.php`
- Create: `app/Models/MaintenanceReminder.php`
- Test: `tests/Feature/MaintenanceReminderModelTest.php`

**Interfaces:**
- Consumes: Task 2 oszlopai (nincs közvetlen függés), a `products` és `users` táblák.
- Produces:
  - `MaintenanceReminderStage` enum, string backed: `Advance = 'advance'`, `Overdue = 'overdue'`, `Manual = 'manual'`; implementálja a `Filament\Support\Contracts\HasLabel` interfészt egy `getLabel(): string` metódussal, hogy a Filament szűrők és badge-ek automatikusan magyar feliratot kapjanak.
  - `MaintenanceReminderStatus` enum, string backed: `Sent = 'sent'`, `Failed = 'failed'`; szintén `HasLabel`, `getLabel(): string` metódussal.
  - `MaintenanceReminder` modell: `product_id`, `user_id`, `email`, `stage`, `stage_key`, `due_date`, `last_maintenance_at`, `sent_at`, `status`, `error`; relációk `product()`, `user()`.
  - Unique index a `(product_id, user_id, due_date, stage, stage_key)` ötösön.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceReminderModelTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;

function reminderAttributes(Product $product, User $user, array $overrides = []): array
{
    return array_merge([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'email' => $user->email,
        'stage' => MaintenanceReminderStage::Advance,
        'stage_key' => 30,
        'due_date' => '2026-03-10',
        'last_maintenance_at' => '2025-03-10',
        'sent_at' => now(),
        'status' => MaintenanceReminderStatus::Sent,
    ], $overrides);
}

it('stores a reminder with casted enums and dates', function (): void {
    $product = Product::factory()->createOne();
    $user = User::factory()->createOne();

    $reminder = MaintenanceReminder::query()->create(reminderAttributes($product, $user));

    expect($reminder->stage)->toBe(MaintenanceReminderStage::Advance)
        ->and($reminder->status)->toBe(MaintenanceReminderStatus::Sent)
        ->and($reminder->due_date->toDateString())->toBe('2026-03-10')
        ->and($reminder->product->is($product))->toBeTrue()
        ->and($reminder->user->is($user))->toBeTrue();
});

it('rejects a duplicate reminder for the same product, recipient, due date and stage', function (): void {
    $product = Product::factory()->createOne();
    $user = User::factory()->createOne();

    MaintenanceReminder::query()->create(reminderAttributes($product, $user));

    expect(fn () => MaintenanceReminder::query()->create(reminderAttributes($product, $user)))
        ->toThrow(QueryException::class);
});

it('allows the same due date for a different stage key', function (): void {
    $product = Product::factory()->createOne();
    $user = User::factory()->createOne();

    MaintenanceReminder::query()->create(reminderAttributes($product, $user));
    MaintenanceReminder::query()->create(reminderAttributes($product, $user, ['stage_key' => 7]));

    expect(MaintenanceReminder::query()->count())->toBe(2);
});

it('allows the same stage for a different recipient', function (): void {
    $product = Product::factory()->createOne();

    MaintenanceReminder::query()->create(reminderAttributes($product, User::factory()->createOne()));
    MaintenanceReminder::query()->create(reminderAttributes($product, User::factory()->createOne()));

    expect(MaintenanceReminder::query()->count())->toBe(2);
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderModelTest`
Expected: FAIL — `Class "App\Enums\MaintenanceReminderStage" not found`

- [ ] **Step 3: Írd meg az enumokat**

`app/Enums/MaintenanceReminderStage.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaintenanceReminderStage: string implements HasLabel
{
    case Advance = 'advance';

    case Overdue = 'overdue';

    case Manual = 'manual';

    public function getLabel(): string
    {
        return match ($this) {
            self::Advance => 'Előzetes',
            self::Overdue => 'Lejárt',
            self::Manual => 'Manuális',
        };
    }
}
```

`app/Enums/MaintenanceReminderStatus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaintenanceReminderStatus: string implements HasLabel
{
    case Sent = 'sent';

    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Sent => 'Elküldve',
            self::Failed => 'Hiba',
        };
    }
}
```

- [ ] **Step 4: Generáld és töltsd ki a migrációt**

Run: `php artisan make:migration create_maintenance_reminders_table --no-interaction`

```php
public function up(): void
{
    Schema::create('maintenance_reminders', function (Blueprint $table): void {
        $table->id();
        $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
        $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
        $table->string('email', 255);
        $table->string('stage', 20);
        $table->unsignedSmallInteger('stage_key');
        $table->date('due_date');
        $table->date('last_maintenance_at')->nullable();
        $table->timestamp('sent_at')->nullable();
        $table->string('status', 20);
        $table->text('error')->nullable();
        $table->timestamps();

        $table->unique(
            ['product_id', 'user_id', 'due_date', 'stage', 'stage_key'],
            'maintenance_reminders_unique_occurrence',
        );
    });
}

public function down(): void
{
    Schema::dropIfExists('maintenance_reminders');
}
```

A fájl tetején: `use App\Models\Product;` és `use App\Models\User;`.

- [ ] **Step 5: Írd meg a modellt**

`app/Models/MaintenanceReminder.php`:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

#[Fillable([
    'product_id',
    'user_id',
    'email',
    'stage',
    'stage_key',
    'due_date',
    'last_maintenance_at',
    'sent_at',
    'status',
    'error',
])]
class MaintenanceReminder extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'stage' => MaintenanceReminderStage::class,
            'status' => MaintenanceReminderStatus::class,
            'stage_key' => 'integer',
            'due_date' => 'immutable_date',
            'last_maintenance_at' => 'immutable_date',
            'sent_at' => 'immutable_datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderModelTest`
Expected: PASS (4 teszt)

- [ ] **Step 7: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/MaintenanceReminderStage.php app/Enums/MaintenanceReminderStatus.php app/Models/MaintenanceReminder.php database/migrations tests/Feature/MaintenanceReminderModelTest.php
git commit -m "feat: add maintenance reminder log model"
```

---

### Task 5: Sablon renderer

**Files:**
- Create: `app/Support/PendingMaintenanceReminder.php`
- Create: `app/Support/MaintenanceReminderTemplateRenderer.php`
- Test: `tests/Feature/MaintenanceReminderTemplateRendererTest.php`

**Interfaces:**
- Consumes: Task 1 `MaintenanceReminderSetting`, Task 3 `MaintenanceSchedule`, Task 4 `MaintenanceReminderStage`.
- Produces:
  - `PendingMaintenanceReminder` readonly DTO: `public Product $product`, `public User $user`, `public MaintenanceReminderStage $stage`, `public int $stageKey`, `public MaintenanceSchedule $schedule`.
  - `MaintenanceReminderTemplateRenderer::render(PendingMaintenanceReminder $reminder, MaintenanceReminderSetting $settings): array{subject: string, body: string}`

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceReminderTemplateRendererTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\Tool;
use App\Models\User;
use App\Support\MaintenanceSchedule;
use App\Support\MaintenanceReminderTemplateRenderer;
use App\Support\PendingMaintenanceReminder;

function pendingReminder(array $productAttributes = [], int $intervalMonths = 12): PendingMaintenanceReminder
{
    $tool = Tool::factory()->createOne(['name' => 'Vaillant ecoTEC']);
    $product = Product::factory()->createOne(array_merge([
        'tool_id' => $tool->id,
        'owner_name' => 'Kiss Béla',
        'serial_number' => 'AB-1234-CDEF',
        'installation_date' => '2024-01-01',
        'maintenance_interval_months' => $intervalMonths,
    ], $productAttributes));

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);

    return new PendingMaintenanceReminder(
        product: $product->fresh(),
        user: User::factory()->createOne(),
        stage: MaintenanceReminderStage::Advance,
        stageKey: 30,
        schedule: MaintenanceSchedule::for($product->fresh()),
    );
}

it('replaces every supported variable', function (): void {
    MaintenanceReminderSetting::current()->update([
        'contact_phone' => '+36 1 234 5678',
        'contact_email' => 'szerviz@example.test',
        'booking_url' => 'https://example.test/foglalas',
        'email_subject' => 'Karbantartás: {{ serial_number }}',
        'email_body' => '{{ owner_name }} | {{ tool_name }} | {{ maintenance_type }} | '
            . '{{ last_maintenance_date }} | {{ due_date }} | {{ contact_phone }} | '
            . '{{ contact_email }} | {{ booking_url }}',
    ]);

    $rendered = (new MaintenanceReminderTemplateRenderer())
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['subject'])->toBe('Karbantartás: AB-1234-CDEF')
        ->and($rendered['body'])->toBe(
            'Kiss Béla | Vaillant ecoTEC | éves | 2025. 03. 10. | 2026. 03. 10. | '
            . '+36 1 234 5678 | szerviz@example.test | https://example.test/foglalas'
        );
});

it('labels a six month interval as half-yearly', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => '{{ maintenance_type }}']);

    $rendered = (new MaintenanceReminderTemplateRenderer())
        ->render(pendingReminder(intervalMonths: 6), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('féléves');
});

it('replaces unknown variables with an empty string', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => 'A[{{ nincs_ilyen }}]B']);

    $rendered = (new MaintenanceReminderTemplateRenderer())
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('A[]B');
});

it('tolerates missing optional contact details', function (): void {
    MaintenanceReminderSetting::current()->update([
        'contact_phone' => null,
        'email_body' => 'Tel: [{{ contact_phone }}]',
    ]);

    $rendered = (new MaintenanceReminderTemplateRenderer())
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('Tel: []');
});

it('handles variables written without spaces', function (): void {
    MaintenanceReminderSetting::current()->update(['email_body' => '{{serial_number}}']);

    $rendered = (new MaintenanceReminderTemplateRenderer())
        ->render(pendingReminder(), MaintenanceReminderSetting::current());

    expect($rendered['body'])->toBe('AB-1234-CDEF');
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderTemplateRendererTest`
Expected: FAIL — `Class "App\Support\PendingMaintenanceReminder" not found`

- [ ] **Step 3: Írd meg a DTO-t**

`app/Support/PendingMaintenanceReminder.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\MaintenanceReminderStage;
use App\Models\Product;
use App\Models\User;

final readonly class PendingMaintenanceReminder
{
    public function __construct(
        public Product $product,
        public User $user,
        public MaintenanceReminderStage $stage,
        public int $stageKey,
        public MaintenanceSchedule $schedule,
    ) {}
}
```

- [ ] **Step 4: Írd meg a renderert**

`app/Support/MaintenanceReminderTemplateRenderer.php`:

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\MaintenanceReminderSetting;

final class MaintenanceReminderTemplateRenderer
{
    private const DATE_FORMAT = 'Y. m. d.';

    /**
     * A tárgy és a törzs feloldott változókkal.
     *
     * @return array{subject: string, body: string}
     */
    public function render(
        PendingMaintenanceReminder $reminder,
        MaintenanceReminderSetting $settings,
    ): array {
        $variables = $this->variables($reminder, $settings);

        return [
            'subject' => $this->replace($settings->email_subject, $variables),
            'body' => $this->replace($settings->email_body, $variables),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function variables(
        PendingMaintenanceReminder $reminder,
        MaintenanceReminderSetting $settings,
    ): array {
        $product = $reminder->product;

        return [
            'owner_name' => (string) ($product->owner_name ?? ''),
            'serial_number' => (string) $product->serial_number,
            'tool_name' => (string) ($product->tool?->name ?? ''),
            'maintenance_type' => $product->maintenance_interval_months === 6 ? 'féléves' : 'éves',
            'last_maintenance_date' => $reminder->schedule->baseDate->format(self::DATE_FORMAT),
            'due_date' => $reminder->schedule->dueDate->format(self::DATE_FORMAT),
            'contact_phone' => (string) ($settings->contact_phone ?? ''),
            'contact_email' => (string) ($settings->contact_email ?? ''),
            'booking_url' => (string) ($settings->booking_url ?? ''),
        ];
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function replace(string $template, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*(\w+)\s*\}\}/',
            fn (array $matches): string => $variables[$matches[1]] ?? '',
            $template,
        );
    }
}
```

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderTemplateRendererTest`
Expected: PASS (5 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Support/PendingMaintenanceReminder.php app/Support/MaintenanceReminderTemplateRenderer.php tests/Feature/MaintenanceReminderTemplateRendererTest.php
git commit -m "feat: render maintenance reminder email templates"
```

---

### Task 6: Az emlékeztető levél

**Files:**
- Create: `app/Mail/MaintenanceReminderMail.php`
- Create: `resources/views/emails/maintenance-reminder.blade.php`
- Test: `tests/Feature/MaintenanceReminderMailTest.php`

**Interfaces:**
- Consumes: Task 5 renderer kimenete (`subject`, `body`), Task 1 beállítások.
- Produces: `new MaintenanceReminderMail(Product $product, string $subjectLine, string $body, ?string $bookingUrl, ?string $contactPhone, ?string $contactEmail)` — `ShouldQueue`, a nézete `emails.maintenance-reminder`.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceReminderMailTest.php`:

```php
<?php

declare(strict_types=1);

use App\Mail\MaintenanceReminderMail;
use App\Models\Product;

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
    expect(reminderMail())->toBeInstanceOf(Illuminate\Contracts\Queue\ShouldQueue::class);
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderMailTest`
Expected: FAIL — `Class "App\Mail\MaintenanceReminderMail" not found`

- [ ] **Step 3: Írd meg a Mailable-t**

Run: `php artisan make:mail MaintenanceReminderMail --no-interaction`

Majd írd felül a tartalmát:

```php
<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceReminderMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Product $product,
        public string $subjectLine,
        public string $body,
        public ?string $bookingUrl,
        public ?string $contactPhone,
        public ?string $contactEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.maintenance-reminder',
            with: [
                'body' => $this->body,
                'bookingUrl' => $this->bookingUrl,
                'contactPhone' => $this->contactPhone,
                'contactEmail' => $this->contactEmail,
            ],
        );
    }
}
```

- [ ] **Step 4: Írd meg a nézetet**

`resources/views/emails/maintenance-reminder.blade.php`:

```blade
<x-mail::message>
{!! nl2br(e($body)) !!}

@if ($bookingUrl)
<x-mail::button :url="$bookingUrl">
Időpont foglalása
</x-mail::button>
@endif

@if ($contactPhone || $contactEmail)
---
@if ($contactPhone)
**Telefon:** {{ $contactPhone }}<br>
@endif
@if ($contactEmail)
**E-mail:** {{ $contactEmail }}
@endif
@endif

Üdvözlettel,<br>
{{ config('app.name') }}
</x-mail::message>
```

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderMailTest`
Expected: PASS (4 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Mail/MaintenanceReminderMail.php resources/views/emails/maintenance-reminder.blade.php tests/Feature/MaintenanceReminderMailTest.php
git commit -m "feat: add maintenance reminder mailable"
```

---

### Task 7: Jogosultsági szűrő és szakasz-döntés

**Files:**
- Create: `app/Services/MaintenanceReminderScheduler.php`
- Test: `tests/Feature/MaintenanceReminderSchedulerRulesTest.php`

**Interfaces:**
- Consumes: Task 1 `MaintenanceReminderSetting::current()`, Task 3 `MaintenanceSchedule::for()`, Task 4 enumok, Task 5 `PendingMaintenanceReminder`.
- Produces:
  - `MaintenanceReminderScheduler::recipientsFor(Product $product): Collection<int, User>` — a `products.user_id` és a `user_product` kapcsolat felhasználói, duplikátum nélkül, csak azok, akiknél `maintenance_reminders_enabled` és van e-mail cím.
  - `MaintenanceReminderScheduler::isProductEligible(Product $product, CarbonImmutable $day): bool` — globális kapcsoló, készülék kapcsoló és élő, kitöltött garancia.
  - `MaintenanceReminderScheduler::resolveStage(MaintenanceSchedule $schedule, CarbonImmutable $day, ?MaintenanceReminderSetting $settings = null): ?array{stage: MaintenanceReminderStage, stage_key: int}`
  - `MaintenanceReminderScheduler::pendingFor(CarbonImmutable $day): Collection<int, PendingMaintenanceReminder>`

Ebben a feladatban még nincs küldés és naplózás — csak a döntés.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceReminderSchedulerRulesTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use App\Services\MaintenanceReminderScheduler;
use Carbon\CarbonImmutable;

/**
 * Készülék 2025-03-10-i karbantartással, tehát 2026-03-10-i esedékességgel,
 * élő garanciával és egy értesíthető címzettel.
 */
function eligibleProduct(array $attributes = []): Product
{
    $product = Product::factory()->createOne(array_merge([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ], $attributes));

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

function scheduler(): MaintenanceReminderScheduler
{
    return app(MaintenanceReminderScheduler::class);
}

function pendingOn(string $day): Illuminate\Support\Collection
{
    return scheduler()->pendingFor(CarbonImmutable::parse($day));
}

it('queues an advance reminder 30 days before the due date', function (): void {
    eligibleProduct();

    $pending = pendingOn('2026-02-08');

    expect($pending)->toHaveCount(1)
        ->and($pending->first()->stage)->toBe(MaintenanceReminderStage::Advance)
        ->and($pending->first()->stageKey)->toBe(30)
        ->and($pending->first()->schedule->dueDate->toDateString())->toBe('2026-03-10');
});

it('queues an advance reminder 7 days before the due date', function (): void {
    eligibleProduct();

    $pending = pendingOn('2026-03-03');

    expect($pending)->toHaveCount(1)
        ->and($pending->first()->stageKey)->toBe(7);
});

it('queues nothing on a day that matches no rule', function (): void {
    eligibleProduct();

    expect(pendingOn('2026-02-20'))->toBeEmpty()
        ->and(pendingOn('2026-03-10'))->toBeEmpty();
});

it('queues overdue reminders every 14 days, at most three times', function (): void {
    eligibleProduct();

    expect(pendingOn('2026-03-24')->first()->stage)->toBe(MaintenanceReminderStage::Overdue)
        ->and(pendingOn('2026-03-24')->first()->stageKey)->toBe(1)
        ->and(pendingOn('2026-04-07')->first()->stageKey)->toBe(2)
        ->and(pendingOn('2026-04-21')->first()->stageKey)->toBe(3)
        ->and(pendingOn('2026-05-05'))->toBeEmpty();
});

it('honours a configured advance schedule', function (): void {
    MaintenanceReminderSetting::current()->update(['advance_days' => [45]]);
    eligibleProduct();

    expect(pendingOn('2026-02-08'))->toBeEmpty()
        ->and(pendingOn('2026-01-24')->first()->stageKey)->toBe(45);
});

it('skips everything when the global switch is off', function (): void {
    MaintenanceReminderSetting::current()->update(['enabled' => false]);
    eligibleProduct();

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products with reminders disabled', function (): void {
    eligibleProduct(['maintenance_reminders_enabled' => false]);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips recipients with reminders disabled', function (): void {
    $product = eligibleProduct();
    $product->users()->first()->update(['maintenance_reminders_enabled' => false]);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products whose warranty has expired', function (): void {
    eligibleProduct(['warrantee_date' => '2026-01-01']);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products with no warranty date', function (): void {
    eligibleProduct(['warrantee_date' => null]);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips products with no recipient', function (): void {
    $product = eligibleProduct();
    $product->users()->detach();

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('skips recipients without an email address', function (): void {
    $product = eligibleProduct();
    $product->users()->first()->update(['email' => '']);

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('falls back to the installation date when there is no maintenance log', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => '2025-05-20',
        'warrantee_date' => '2030-01-01',
    ]);
    $product->users()->attach(User::factory()->createOne());

    expect(pendingOn('2026-04-20')->first()->schedule->dueDate->toDateString())
        ->toBe('2026-05-20');
});

it('skips products with neither a maintenance log nor an installation date', function (): void {
    $product = Product::factory()->createOne([
        'installation_date' => null,
        'warrantee_date' => '2030-01-01',
    ]);
    $product->users()->attach(User::factory()->createOne());

    expect(pendingOn('2026-02-08'))->toBeEmpty();
});

it('queues one reminder per recipient', function (): void {
    $product = eligibleProduct();
    $product->users()->attach(User::factory()->createOne());

    expect(pendingOn('2026-02-08'))->toHaveCount(2);
});

it('does not duplicate a recipient linked both directly and through the pivot', function (): void {
    $product = eligibleProduct();
    $user = $product->users()->first();
    $product->update(['user_id' => $user->id]);

    expect(pendingOn('2026-02-08'))->toHaveCount(1);
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderSchedulerRulesTest`
Expected: FAIL — `Target class [App\Services\MaintenanceReminderScheduler] does not exist`

- [ ] **Step 3: Írd meg a schedulert**

`app/Services/MaintenanceReminderScheduler.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MaintenanceReminderStage;
use App\Models\MaintenanceReminderSetting;
use App\Models\Product;
use App\Models\User;
use App\Support\MaintenanceSchedule;
use App\Support\PendingMaintenanceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MaintenanceReminderScheduler
{
    /**
     * Az adott napra esedékes emlékeztetők, készülék × címzett bontásban.
     *
     * @return Collection<int, PendingMaintenanceReminder>
     */
    public function pendingFor(CarbonImmutable $day): Collection
    {
        $settings = MaintenanceReminderSetting::current();

        if (! $settings->enabled) {
            return collect();
        }

        $day = $day->startOfDay();

        return Product::query()
            ->where('maintenance_reminders_enabled', true)
            ->whereNotNull('warrantee_date')
            ->whereDate('warrantee_date', '>=', $day->toDateString())
            ->get()
            ->flatMap(function (Product $product) use ($day, $settings): Collection {
                $schedule = MaintenanceSchedule::for($product);

                if ($schedule === null) {
                    return collect();
                }

                $stage = $this->resolveStage($schedule, $day, $settings);

                if ($stage === null) {
                    return collect();
                }

                return $this->recipientsFor($product)->map(
                    fn (User $user): PendingMaintenanceReminder => new PendingMaintenanceReminder(
                        product: $product,
                        user: $user,
                        stage: $stage['stage'],
                        stageKey: $stage['stage_key'],
                        schedule: $schedule,
                    ),
                );
            })
            ->values();
    }

    /**
     * A készülék értesíthető címzettjei, duplikátum nélkül.
     *
     * @return Collection<int, User>
     */
    public function recipientsFor(Product $product): Collection
    {
        return $product->users
            ->when(
                $product->user_id !== null,
                fn (Collection $users): Collection => $users->concat(
                    User::query()->whereKey($product->user_id)->get(),
                ),
            )
            ->unique('id')
            ->filter(fn (User $user): bool => $user->maintenance_reminders_enabled
                && filled($user->email))
            ->values();
    }

    /**
     * A készülék önmagában jogosult-e emlékeztetőre, a naptól függetlenül.
     */
    public function isProductEligible(Product $product, CarbonImmutable $day): bool
    {
        return MaintenanceReminderSetting::current()->enabled
            && $product->maintenance_reminders_enabled
            && $product->warrantee_date !== null
            && CarbonImmutable::parse($product->warrantee_date)->startOfDay()->greaterThanOrEqualTo($day->startOfDay());
    }

    /**
     * Melyik emlékeztető szakasz esik erre a napra, ha esik egyáltalán.
     *
     * @return array{stage: MaintenanceReminderStage, stage_key: int}|null
     */
    public function resolveStage(
        MaintenanceSchedule $schedule,
        CarbonImmutable $day,
        ?MaintenanceReminderSetting $settings = null,
    ): ?array {
        $settings ??= MaintenanceReminderSetting::current();
        $day = $day->startOfDay();
        $due = $schedule->dueDate->startOfDay();

        foreach ($settings->advance_days as $days) {
            if ($day->equalTo($due->subDays((int) $days))) {
                return ['stage' => MaintenanceReminderStage::Advance, 'stage_key' => (int) $days];
            }
        }

        if ($day->greaterThan($due)) {
            $elapsed = (int) $due->diffInDays($day);
            $occurrence = intdiv($elapsed, $settings->overdue_repeat_days);

            if ($occurrence >= 1 && $occurrence <= $settings->overdue_max_count) {
                return ['stage' => MaintenanceReminderStage::Overdue, 'stage_key' => $occurrence];
            }
        }

        return null;
    }
}
```

- [ ] **Step 4: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderSchedulerRulesTest`
Expected: PASS (16 teszt)

Ha az „overdue every 14 days" teszt bukik, ellenőrizd, hogy a `diffInDays` pozitív egészt ad-e vissza a `$due` → `$day` irányban; szükség esetén `abs()` nélkül, `$due->diffInDays($day)` sorrendben kell hívni.

- [ ] **Step 5: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/MaintenanceReminderScheduler.php tests/Feature/MaintenanceReminderSchedulerRulesTest.php
git commit -m "feat: resolve which maintenance reminders are due on a given day"
```

---

### Task 8: Küldés, naplózás, hibakezelés

**Files:**
- Modify: `app/Services/MaintenanceReminderScheduler.php`
- Test: `tests/Feature/MaintenanceReminderSendingTest.php`

**Interfaces:**
- Consumes: Task 5 renderer, Task 6 `MaintenanceReminderMail`, Task 7 `pendingFor()`.
- Produces:
  - `MaintenanceReminderScheduler::send(PendingMaintenanceReminder $reminder): ?MaintenanceReminder` — `null`, ha már ki lett küldve (`sent` státusszal naplózva); egyébként a napló rekord.
  - `MaintenanceReminderScheduler::run(CarbonImmutable $day): int` — az adott napra kiküldött levelek száma.
  - `MaintenanceReminderScheduler::sendManually(Product $product): int` — a manuálisan kiküldött levelek száma; a dátum-egyezés feltételt kihagyja, a jogosultsági szűrőt nem.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/MaintenanceReminderSendingTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use App\Services\MaintenanceReminderScheduler;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

function sendableProduct(array $attributes = []): Product
{
    $product = Product::factory()->createOne(array_merge([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ], $attributes));

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

function runOn(string $day): int
{
    return app(MaintenanceReminderScheduler::class)->run(CarbonImmutable::parse($day));
}

it('sends the reminder and logs it as sent', function (): void {
    $product = sendableProduct();

    expect(runOn('2026-02-08'))->toBe(1);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);

    $reminder = MaintenanceReminder::query()->sole();

    expect($reminder->product_id)->toBe($product->id)
        ->and($reminder->stage)->toBe(MaintenanceReminderStage::Advance)
        ->and($reminder->stage_key)->toBe(30)
        ->and($reminder->status)->toBe(MaintenanceReminderStatus::Sent)
        ->and($reminder->due_date->toDateString())->toBe('2026-03-10')
        ->and($reminder->last_maintenance_at->toDateString())->toBe('2025-03-10')
        ->and($reminder->sent_at)->not->toBeNull()
        ->and($reminder->email)->toBe($product->users()->first()->email);
});

it('does not send the same reminder twice', function (): void {
    sendableProduct();

    runOn('2026-02-08');
    expect(runOn('2026-02-08'))->toBe(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->count())->toBe(1);
});

it('sends the 7 day reminder after the 30 day one', function (): void {
    sendableProduct();

    runOn('2026-02-08');
    runOn('2026-03-03');

    Mail::assertQueued(MaintenanceReminderMail::class, 2);
    expect(MaintenanceReminder::query()->count())->toBe(2);
});

it('logs a failure and keeps processing the other products', function (): void {
    sendableProduct(['serial_number' => 'FAIL-0001']);
    sendableProduct(['serial_number' => 'OK-0001']);

    Mail::shouldReceive('to')->andReturnUsing(function (string $email) {
        static $call = 0;
        $call++;

        if ($call === 1) {
            throw new RuntimeException('SMTP connection refused');
        }

        return Mockery::mock(['send' => null, 'queue' => null]);
    });

    expect(runOn('2026-02-08'))->toBe(1);

    $failed = MaintenanceReminder::query()
        ->where('status', MaintenanceReminderStatus::Failed)
        ->sole();

    expect($failed->error)->toContain('SMTP connection refused')
        ->and($failed->sent_at)->toBeNull()
        ->and(MaintenanceReminder::query()->count())->toBe(2);
});

it('retries a previously failed reminder without creating a duplicate row', function (): void {
    $product = sendableProduct();

    MaintenanceReminder::query()->create([
        'product_id' => $product->id,
        'user_id' => $product->users()->first()->id,
        'email' => $product->users()->first()->email,
        'stage' => MaintenanceReminderStage::Advance,
        'stage_key' => 30,
        'due_date' => '2026-03-10',
        'last_maintenance_at' => '2025-03-10',
        'status' => MaintenanceReminderStatus::Failed,
        'error' => 'SMTP connection refused',
    ]);

    expect(runOn('2026-02-08'))->toBe(1)
        ->and(MaintenanceReminder::query()->count())->toBe(1)
        ->and(MaintenanceReminder::query()->sole()->status)->toBe(MaintenanceReminderStatus::Sent)
        ->and(MaintenanceReminder::query()->sole()->error)->toBeNull();
});

it('stops the overdue series once a new maintenance log is recorded', function (): void {
    $product = sendableProduct();

    runOn('2026-03-24');
    expect(MaintenanceReminder::query()->count())->toBe(1);

    ProductLog::factory()->on('2026-03-25 09:00:00')->createOne(['product_id' => $product->id]);

    expect(runOn('2026-04-07'))->toBe(0);
});

it('sends manually regardless of the day, and allows repeated manual sends', function (): void {
    $product = sendableProduct();
    $scheduler = app(MaintenanceReminderScheduler::class);

    expect($scheduler->sendManually($product))->toBe(1)
        ->and($scheduler->sendManually($product))->toBe(1);

    Mail::assertQueued(MaintenanceReminderMail::class, 2);

    $reminders = MaintenanceReminder::query()
        ->where('stage', MaintenanceReminderStage::Manual)
        ->orderBy('stage_key')
        ->get();

    expect($reminders)->toHaveCount(2)
        ->and($reminders->pluck('stage_key')->all())->toBe([1, 2]);
});

it('refuses a manual send for an ineligible product', function (): void {
    $product = sendableProduct(['warrantee_date' => '2020-01-01']);

    expect(app(MaintenanceReminderScheduler::class)->sendManually($product))->toBe(0);

    Mail::assertNothingQueued();
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderSendingTest`
Expected: FAIL — `Call to undefined method App\Services\MaintenanceReminderScheduler::run()`

- [ ] **Step 3: Egészítsd ki a schedulert**

`app/Services/MaintenanceReminderScheduler.php` — az importok közé:

```php
use App\Enums\MaintenanceReminderStatus;
use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Support\MaintenanceReminderTemplateRenderer;
use Illuminate\Support\Facades\Mail;
use Throwable;
```

Konstruktor az osztály elejére:

```php
    public function __construct(
        private MaintenanceReminderTemplateRenderer $renderer,
    ) {}
```

Új metódusok az osztály végére:

```php
    /**
     * Kiküldi az adott napra esedékes összes emlékeztetőt, és visszaadja a sikeres levelek számát.
     */
    public function run(CarbonImmutable $day): int
    {
        return $this->pendingFor($day)
            ->filter(fn (PendingMaintenanceReminder $reminder): bool => $this->send($reminder)?->status === MaintenanceReminderStatus::Sent)
            ->count();
    }

    /**
     * Egy készülék összes címzettjének azonnali emlékeztetőt küld, a naptári feltételtől függetlenül.
     */
    public function sendManually(Product $product): int
    {
        $day = CarbonImmutable::now()->startOfDay();

        if (! $this->isProductEligible($product, $day)) {
            return 0;
        }

        $schedule = MaintenanceSchedule::for($product);

        if ($schedule === null) {
            return 0;
        }

        return $this->recipientsFor($product)
            ->filter(function (User $user) use ($product, $schedule): bool {
                $reminder = new PendingMaintenanceReminder(
                    product: $product,
                    user: $user,
                    stage: MaintenanceReminderStage::Manual,
                    stageKey: $this->nextManualStageKey($product, $user, $schedule),
                    schedule: $schedule,
                );

                return $this->send($reminder)?->status === MaintenanceReminderStatus::Sent;
            })
            ->count();
    }

    /**
     * Kiküld egy emlékeztetőt és naplózza az eredményt.
     * Null, ha korábban már sikeresen kiment ugyanez a szakasz.
     */
    public function send(PendingMaintenanceReminder $reminder): ?MaintenanceReminder
    {
        $log = MaintenanceReminder::query()->firstOrNew([
            'product_id' => $reminder->product->getKey(),
            'user_id' => $reminder->user->getKey(),
            'due_date' => $reminder->schedule->dueDate->toDateString(),
            'stage' => $reminder->stage,
            'stage_key' => $reminder->stageKey,
        ]);

        if ($log->exists && $log->status === MaintenanceReminderStatus::Sent) {
            return null;
        }

        $settings = MaintenanceReminderSetting::current();
        $rendered = $this->renderer->render($reminder, $settings);

        $log->fill([
            'email' => $reminder->user->email,
            'last_maintenance_at' => $reminder->schedule->fromMaintenanceLog
                ? $reminder->schedule->baseDate->toDateString()
                : null,
        ]);

        try {
            Mail::to($reminder->user->email)->send(new MaintenanceReminderMail(
                product: $reminder->product,
                subjectLine: $rendered['subject'],
                body: $rendered['body'],
                bookingUrl: $settings->booking_url,
                contactPhone: $settings->contact_phone,
                contactEmail: $settings->contact_email,
            ));

            $log->fill([
                'status' => MaintenanceReminderStatus::Sent,
                'sent_at' => CarbonImmutable::now(),
                'error' => null,
            ]);
        } catch (Throwable $exception) {
            $log->fill([
                'status' => MaintenanceReminderStatus::Failed,
                'sent_at' => null,
                'error' => $exception->getMessage(),
            ]);
        }

        $log->save();

        return $log;
    }

    /**
     * A következő szabad sorszám a manuális küldésekhez, hogy az unique index ne ütközzön.
     */
    private function nextManualStageKey(Product $product, User $user, MaintenanceSchedule $schedule): int
    {
        return MaintenanceReminder::query()
            ->where('product_id', $product->getKey())
            ->where('user_id', $user->getKey())
            ->where('due_date', $schedule->dueDate->toDateString())
            ->where('stage', MaintenanceReminderStage::Manual)
            ->max('stage_key') + 1;
    }
```

Figyelem: a `firstOrNew()` a `sent` státuszú rekordot kiszűri, a `failed` státuszút pedig újrapróbálja ugyanabban a sorban — így az unique index sosem sérül.

Mivel a `MaintenanceReminderMail` `ShouldQueue`, a `Mail::to(...)->send(...)` valójában sorba állítja a levelet. A `Mail::fake()` ezt `assertQueued`-dal ellenőrzi.

- [ ] **Step 4: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderSendingTest`
Expected: PASS (8 teszt)

- [ ] **Step 5: Futtasd a korábbi teszteket is, hogy nem tört el semmi**

Run: `php artisan test --compact --filter=MaintenanceReminder`
Expected: PASS

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/MaintenanceReminderScheduler.php tests/Feature/MaintenanceReminderSendingTest.php
git commit -m "feat: send and log maintenance reminder emails"
```

---

### Task 9: Artisan command és napi ütemezés

**Files:**
- Create: `app/Console/Commands/SendMaintenanceRemindersCommand.php`
- Create: `routes/console.php`
- Test: `tests/Feature/SendMaintenanceRemindersCommandTest.php`

**Interfaces:**
- Consumes: Task 8 `MaintenanceReminderScheduler::run()` és `pendingFor()`.
- Produces: `maintenance:send-reminders {--dry-run} {--catch-up=1}` Artisan parancs, naponta 08:00-kor ütemezve.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/SendMaintenanceRemindersCommandTest.php`:

```php
<?php

declare(strict_types=1);

use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

function commandProduct(): Product
{
    $product = Product::factory()->createOne([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ]);

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

it('sends the reminders due today', function (): void {
    commandProduct();
    $this->travelTo('2026-02-08 08:00:00');

    $this->artisan('maintenance:send-reminders')
        ->expectsOutputToContain('1')
        ->assertExitCode(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
});

it('sends nothing and logs nothing in dry run mode', function (): void {
    commandProduct();
    $this->travelTo('2026-02-08 08:00:00');

    $this->artisan('maintenance:send-reminders', ['--dry-run' => true])
        ->assertExitCode(0);

    Mail::assertNothingQueued();
    expect(MaintenanceReminder::query()->count())->toBe(0);
});

it('catches up a missed day', function (): void {
    commandProduct();
    $this->travelTo('2026-02-09 08:00:00');

    $this->artisan('maintenance:send-reminders', ['--catch-up' => 1])
        ->assertExitCode(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->sole()->stage_key)->toBe(30);
});

it('does not resend on catch up when the reminder already went out', function (): void {
    commandProduct();

    $this->travelTo('2026-02-08 08:00:00');
    $this->artisan('maintenance:send-reminders')->assertExitCode(0);

    $this->travelTo('2026-02-09 08:00:00');
    $this->artisan('maintenance:send-reminders', ['--catch-up' => 1])->assertExitCode(0);

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->count())->toBe(1);
});

it('is scheduled to run daily', function (): void {
    $events = collect(app(Schedule::class)->events())
        ->filter(fn (Event $event): bool => str_contains(
            (string) $event->command,
            'maintenance:send-reminders',
        ));

    expect($events)->toHaveCount(1)
        ->and($events->first()->expression)->toBe('0 8 * * *');
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=SendMaintenanceRemindersCommandTest`
Expected: FAIL — `The command "maintenance:send-reminders" does not exist.`

- [ ] **Step 3: Írd meg a parancsot**

Run: `php artisan make:command SendMaintenanceRemindersCommand --no-interaction`

Majd írd felül a tartalmát:

```php
<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MaintenanceReminderScheduler;
use App\Support\PendingMaintenanceReminder;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendMaintenanceRemindersCommand extends Command
{
    protected $signature = 'maintenance:send-reminders
                            {--dry-run : Csak kiírja, mi menne ki, nem küld és nem naplóz}
                            {--catch-up=1 : Ennyi korábbi napot is ellenőriz visszamenőleg}';

    protected $description = 'Karbantartás emlékeztető e-mailek kiküldése';

    public function handle(MaintenanceReminderScheduler $scheduler): int
    {
        $catchUp = max(0, (int) $this->option('catch-up'));
        $today = CarbonImmutable::now()->startOfDay();
        $sent = 0;

        for ($offset = $catchUp; $offset >= 0; $offset--) {
            $day = $today->subDays($offset);

            if ($this->option('dry-run')) {
                $scheduler->pendingFor($day)->each(
                    fn (PendingMaintenanceReminder $reminder) => $this->line(sprintf(
                        '%s | %s | %s | %s (%d)',
                        $day->toDateString(),
                        $reminder->product->serial_number,
                        $reminder->user->email,
                        $reminder->stage->getLabel(),
                        $reminder->stageKey,
                    )),
                );

                continue;
            }

            $sent += $scheduler->run($day);
        }

        if ($this->option('dry-run')) {
            $this->info('Próbafutás, nem ment ki levél.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Kiküldött emlékeztetők: %d', $sent));

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Regisztráld az ütemezést**

`routes/console.php` (új fájl):

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('maintenance:send-reminders')
    ->dailyAt('08:00')
    ->withoutOverlapping();
```

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test --compact --filter=SendMaintenanceRemindersCommandTest`
Expected: PASS (5 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Console/Commands/SendMaintenanceRemindersCommand.php routes/console.php tests/Feature/SendMaintenanceRemindersCommandTest.php
git commit -m "feat: schedule the daily maintenance reminder command"
```

---

### Task 10: Napló lista a Filament adminban

**Files:**
- Create: `app/Filament/Resources/MaintenanceReminders/MaintenanceReminderResource.php`
- Create: `app/Filament/Resources/MaintenanceReminders/Pages/ListMaintenanceReminders.php`
- Create: `app/Filament/Resources/MaintenanceReminders/Tables/MaintenanceReminderTable.php`
- Test: `tests/Feature/Filament/MaintenanceReminderResourceTest.php`

**Interfaces:**
- Consumes: Task 4 `MaintenanceReminder` modell és enumok.
- Produces: csak olvasható `/admin/maintenance-reminders` lista, státusz és szakasz szűrővel.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/Filament/MaintenanceReminderResourceTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Filament\Resources\MaintenanceReminders\MaintenanceReminderResource;
use App\Filament\Resources\MaintenanceReminders\Pages\ListMaintenanceReminders;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

function adminUser(): User
{
    Role::findOrCreate('Admin', 'web');
    $user = User::factory()->createOne();
    $user->assignRole('Admin');

    return $user;
}

function reminderRecord(MaintenanceReminderStatus $status, int $stageKey = 30): MaintenanceReminder
{
    return MaintenanceReminder::query()->create([
        'product_id' => Product::factory()->createOne()->id,
        'user_id' => User::factory()->createOne()->id,
        'email' => fake()->unique()->safeEmail(),
        'stage' => MaintenanceReminderStage::Advance,
        'stage_key' => $stageKey,
        'due_date' => '2026-03-10',
        'last_maintenance_at' => '2025-03-10',
        'sent_at' => $status === MaintenanceReminderStatus::Sent ? now() : null,
        'status' => $status,
        'error' => $status === MaintenanceReminderStatus::Failed ? 'SMTP hiba' : null,
    ]);
}

beforeEach(function (): void {
    actingAs(adminUser());
});

it('lists the sent reminders', function (): void {
    $reminders = collect([
        reminderRecord(MaintenanceReminderStatus::Sent, 30),
        reminderRecord(MaintenanceReminderStatus::Failed, 7),
    ]);

    livewire(ListMaintenanceReminders::class)
        ->assertCanSeeTableRecords($reminders);
});

it('filters by status', function (): void {
    $sent = reminderRecord(MaintenanceReminderStatus::Sent, 30);
    $failed = reminderRecord(MaintenanceReminderStatus::Failed, 7);

    livewire(ListMaintenanceReminders::class)
        ->filterTable('status', MaintenanceReminderStatus::Failed->value)
        ->assertCanSeeTableRecords([$failed])
        ->assertCanNotSeeTableRecords([$sent]);
});

it('is read only', function (): void {
    expect(MaintenanceReminderResource::canCreate())->toBeFalse()
        ->and(MaintenanceReminderResource::getPages())->toHaveKeys(['index'])
        ->and(MaintenanceReminderResource::getPages())->toHaveCount(1);
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderResourceTest`
Expected: FAIL — `Class "App\Filament\Resources\MaintenanceReminders\..." not found`

- [ ] **Step 3: Írd meg a tábla definíciót**

`app/Filament/Resources/MaintenanceReminders/Tables/MaintenanceReminderTable.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceReminders\Tables;

use App\Enums\MaintenanceReminderStage;
use App\Enums\MaintenanceReminderStatus;
use App\Models\MaintenanceReminder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceReminderTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.serial_number')
                    ->label('Gyári szám')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Ügyfél')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail cím')
                    ->searchable(),
                TextColumn::make('stage')
                    ->label('Szakasz')
                    ->badge(),
                TextColumn::make('stage_key')
                    ->label('Sorszám / nap')
                    ->numeric(),
                TextColumn::make('last_maintenance_at')
                    ->label('Előző karbantartás')
                    ->date()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Esedékesség')
                    ->date()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label('Kiküldve')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Státusz')
                    ->badge()
                    ->color(fn (MaintenanceReminderStatus $state): string => $state === MaintenanceReminderStatus::Sent
                        ? 'success'
                        : 'danger'),
                TextColumn::make('error')
                    ->label('Hibaüzenet')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Státusz')
                    ->options(MaintenanceReminderStatus::class),
                SelectFilter::make('stage')
                    ->label('Szakasz')
                    ->options(MaintenanceReminderStage::class),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
```

A badge-ek és a szűrők feliratait a Task 4-ben megvalósított `HasLabel` interfész adja, ezért nem kell `formatStateUsing()`.

- [ ] **Step 4: Írd meg az erőforrást és a lista oldalt**

`app/Filament/Resources/MaintenanceReminders/MaintenanceReminderResource.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceReminders;

use App\Filament\Resources\MaintenanceReminders\Pages\ListMaintenanceReminders;
use App\Filament\Resources\MaintenanceReminders\Tables\MaintenanceReminderTable;
use App\Models\MaintenanceReminder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class MaintenanceReminderResource extends Resource
{
    protected static ?string $model = MaintenanceReminder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'Karbantartás emlékeztetők';

    protected static ?string $modelLabel = 'Karbantartás emlékeztető';

    protected static ?string $pluralModelLabel = 'Karbantartás emlékeztetők';

    #[Override]
    public static function table(Table $table): Table
    {
        return MaintenanceReminderTable::make($table);
    }

    #[Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[Override]
    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceReminders::route('/'),
        ];
    }
}
```

`app/Filament/Resources/MaintenanceReminders/Pages/ListMaintenanceReminders.php`:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Resources\MaintenanceReminders\Pages;

use App\Filament\Resources\MaintenanceReminders\MaintenanceReminderResource;
use Filament\Resources\Pages\ListRecords;
use Override;

class ListMaintenanceReminders extends ListRecords
{
    protected static string $resource = MaintenanceReminderResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [];
    }
}
```

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderResourceTest`
Expected: PASS (3 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/MaintenanceReminders tests/Feature/Filament/MaintenanceReminderResourceTest.php
git commit -m "feat: list maintenance reminders in the admin panel"
```

---

### Task 11: Beállítás- és sablonszerkesztő oldal

**Files:**
- Create: `app/Filament/Pages/MaintenanceReminderSettingsPage.php`
- Create: `resources/views/filament/pages/maintenance-reminder-settings.blade.php`
- Test: `tests/Feature/Filament/MaintenanceReminderSettingsPageTest.php`

**Interfaces:**
- Consumes: Task 1 `MaintenanceReminderSetting::current()`, Task 3 `MaintenanceSchedule::for()`, Task 5 `MaintenanceReminderTemplateRenderer` és `PendingMaintenanceReminder`.
- Produces: `/admin/maintenance-reminder-settings` oldal, ami menti a globális beállításokat és a sablont, valamint egy `preview()` metódussal élő előnézetet ad az első alkalmas készülék adataival (`public ?string $previewSubject` és `public ?string $previewBody`).

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/Filament/MaintenanceReminderSettingsPageTest.php`:

```php
<?php

declare(strict_types=1);

use App\Filament\Pages\MaintenanceReminderSettingsPage;
use App\Models\MaintenanceReminderSetting;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::findOrCreate('Admin', 'web');
    $user = User::factory()->createOne();
    $user->assignRole('Admin');

    actingAs($user);
});

it('fills the form with the current settings', function (): void {
    MaintenanceReminderSetting::current()->update(['contact_phone' => '+36 1 234 5678']);

    livewire(MaintenanceReminderSettingsPage::class)
        ->assertSchemaStateSet([
            'contact_phone' => '+36 1 234 5678',
            'overdue_repeat_days' => 14,
        ]);
});

it('saves the settings and the template', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'enabled' => false,
            'advance_days' => ['45', '14'],
            'overdue_repeat_days' => 21,
            'overdue_max_count' => 2,
            'contact_phone' => '+36 1 111 2222',
            'contact_email' => 'szerviz@example.test',
            'booking_url' => 'https://example.test/foglalas',
            'email_subject' => 'Új tárgy {{ serial_number }}',
            'email_body' => 'Új törzs {{ due_date }}',
        ])
        ->call('save')
        ->assertNotified()
        ->assertHasNoFormErrors();

    $settings = MaintenanceReminderSetting::current();

    expect($settings->enabled)->toBeFalse()
        ->and($settings->advance_days)->toBe([45, 14])
        ->and($settings->overdue_repeat_days)->toBe(21)
        ->and($settings->overdue_max_count)->toBe(2)
        ->and($settings->email_subject)->toBe('Új tárgy {{ serial_number }}')
        ->and($settings->email_body)->toBe('Új törzs {{ due_date }}');
});

it('requires a subject and a body', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'email_subject' => null,
            'email_body' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'email_subject' => 'required',
            'email_body' => 'required',
        ]);
});

it('previews the template with a sample product', function (): void {
    $tool = Tool::factory()->createOne(['name' => 'Vaillant ecoTEC']);
    Product::factory()->createOne([
        'tool_id' => $tool->id,
        'owner_name' => 'Kiss Béla',
        'serial_number' => 'AB-1234-CDEF',
        'installation_date' => '2025-02-14',
    ]);

    livewire(MaintenanceReminderSettingsPage::class)
        ->fillForm([
            'email_subject' => 'Tárgy {{ serial_number }}',
            'email_body' => '{{ owner_name }} / {{ tool_name }} / {{ due_date }}',
        ])
        ->call('preview')
        ->assertSet('previewSubject', 'Tárgy AB-1234-CDEF')
        ->assertSet('previewBody', 'Kiss Béla / Vaillant ecoTEC / 2026. 02. 14.');
});

it('warns when there is no product to preview with', function (): void {
    livewire(MaintenanceReminderSettingsPage::class)
        ->call('preview')
        ->assertNotified()
        ->assertSet('previewBody', null);
});
```

A teszt fájl importjai közé kell még: `use App\Models\Product;` és `use App\Models\Tool;`.

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderSettingsPageTest`
Expected: FAIL — `Class "App\Filament\Pages\MaintenanceReminderSettingsPage" not found`

- [ ] **Step 3: Írd meg az oldalt**

Run: `php artisan make:filament-page MaintenanceReminderSettingsPage --no-interaction`

Majd írd felül a tartalmát:

```php
<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\MaintenanceReminderSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class MaintenanceReminderSettingsPage extends Page
{
    protected string $view = 'filament.pages.maintenance-reminder-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Emlékeztető beállítások';

    protected static ?string $title = 'Karbantartás emlékeztető beállítások';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public ?string $previewSubject = null;

    public ?string $previewBody = null;

    public function mount(): void
    {
        $this->form->fill(MaintenanceReminderSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([
                    Section::make('Ütemezés')
                        ->schema([
                            Toggle::make('enabled')
                                ->label('Emlékeztetők küldése bekapcsolva'),
                            TagsInput::make('advance_days')
                                ->label('Előidők (nap)')
                                ->helperText('Hány nappal az esedékesség előtt menjen ki emlékeztető.')
                                ->placeholder('30')
                                ->columnSpanFull(),
                            TextInput::make('overdue_repeat_days')
                                ->label('Lejárt emlékeztető ismétlése (nap)')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                            TextInput::make('overdue_max_count')
                                ->label('Lejárt emlékeztetők maximális száma')
                                ->numeric()
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(2),
                    Section::make('Kapcsolatfelvétel')
                        ->schema([
                            TextInput::make('contact_phone')
                                ->label('Telefon')
                                ->tel()
                                ->maxLength(100),
                            TextInput::make('contact_email')
                                ->label('E-mail')
                                ->email()
                                ->maxLength(255),
                            TextInput::make('booking_url')
                                ->label('Időpontfoglaló link')
                                ->url()
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),
                    Section::make('E-mail sablon')
                        ->schema([
                            TextInput::make('email_subject')
                                ->label('Tárgy')
                                ->required()
                                ->maxLength(255),
                            Textarea::make('email_body')
                                ->label('Törzs')
                                ->required()
                                ->rows(14)
                                ->helperText(
                                    'Használható változók: {{ owner_name }}, {{ serial_number }}, '
                                    . '{{ tool_name }}, {{ maintenance_type }}, {{ last_maintenance_date }}, '
                                    . '{{ due_date }}, {{ contact_phone }}, {{ contact_email }}, {{ booking_url }}'
                                ),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Mentés')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                            Action::make('preview')
                                ->label('Előnézet')
                                ->color('gray')
                                ->action('preview'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $data['advance_days'] = collect($data['advance_days'] ?? [])
            ->map(fn (int|string $days): int => (int) $days)
            ->filter(fn (int $days): bool => $days > 0)
            ->values()
            ->all();

        MaintenanceReminderSetting::current()->update($data);

        Notification::make()
            ->success()
            ->title('A beállítások elmentve')
            ->send();
    }

    /**
     * Az űrlap aktuális (még nem mentett) állapotával rendereli a sablont
     * az első olyan készülék adataival, amelyre számítható esedékesség.
     */
    public function preview(): void
    {
        $data = $this->form->getState();

        $product = Product::query()
            ->whereNotNull('installation_date')
            ->oldest('id')
            ->first();

        $schedule = $product === null ? null : MaintenanceSchedule::for($product);

        if ($schedule === null) {
            $this->previewSubject = null;
            $this->previewBody = null;

            Notification::make()
                ->warning()
                ->title('Nincs mintaként használható készülék')
                ->body('Az előnézethez legalább egy beüzemelési dátummal rendelkező készülék szükséges.')
                ->send();

            return;
        }

        $settings = new MaintenanceReminderSetting($data);

        $rendered = app(MaintenanceReminderTemplateRenderer::class)->render(
            new PendingMaintenanceReminder(
                product: $product,
                user: new User(['name' => 'Minta Ügyfél', 'email' => 'minta@example.test']),
                stage: MaintenanceReminderStage::Advance,
                stageKey: (int) ($settings->advance_days[0] ?? 30),
                schedule: $schedule,
            ),
            $settings,
        );

        $this->previewSubject = $rendered['subject'];
        $this->previewBody = $rendered['body'];
    }
}
```

Az importok közé kell még:

```php
use App\Enums\MaintenanceReminderStage;
use App\Models\Product;
use App\Models\User;
use App\Support\MaintenanceReminderTemplateRenderer;
use App\Support\MaintenanceSchedule;
use App\Support\PendingMaintenanceReminder;
```

- [ ] **Step 4: Írd meg a nézetet**

`resources/views/filament/pages/maintenance-reminder-settings.blade.php`:

```blade
<x-filament::page>
    {{ $this->form }}

    @if ($previewBody !== null)
        <x-filament::section :heading="'Előnézet'">
            <p class="font-semibold">{{ $previewSubject }}</p>
            <p class="mt-4 whitespace-pre-line">{{ $previewBody }}</p>
        </x-filament::section>
    @endif
</x-filament::page>
```

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderSettingsPageTest`
Expected: PASS (5 teszt)

- [ ] **Step 6: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Pages/MaintenanceReminderSettingsPage.php resources/views/filament/pages/maintenance-reminder-settings.blade.php tests/Feature/Filament/MaintenanceReminderSettingsPageTest.php
git commit -m "feat: add maintenance reminder settings page"
```

---

### Task 12: Készülék és ügyfél kapcsolók, manuális küldés

**Files:**
- Modify: `app/Filament/Resources/Products/Schemas/ProductFormSchema.php`
- Modify: `app/Filament/Resources/Products/Tables/ProductTable.php`
- Modify: `app/Filament/Resources/Users/Schemas/UserFormSchema.php`
- Test: `tests/Feature/Filament/MaintenanceReminderProductActionsTest.php`

**Interfaces:**
- Consumes: Task 2 oszlopai, Task 3 `Product::nextMaintenanceDueDate()`, Task 8 `MaintenanceReminderScheduler::sendManually()`.
- Produces: intervallum és kapcsoló mezők a készülék űrlapon, ügyfél kapcsoló a felhasználó űrlapon, „Következő esedékesség" oszlop és `sendMaintenanceReminder` akció a készülék táblán.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/Filament/MaintenanceReminderProductActionsTest.php`:

```php
<?php

declare(strict_types=1);

use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Mail\MaintenanceReminderMail;
use App\Models\MaintenanceReminder;
use App\Models\Product;
use App\Models\ProductLog;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Mail::fake();

    Role::findOrCreate('Admin', 'web');
    $admin = User::factory()->createOne();
    $admin->assignRole('Admin');

    actingAs($admin);
});

function remindableProduct(): Product
{
    $product = Product::factory()->createOne([
        'installation_date' => '2024-01-01',
        'warrantee_date' => '2030-01-01',
    ]);

    ProductLog::factory()->on('2025-03-10 09:00:00')->createOne(['product_id' => $product->id]);
    $product->users()->attach(User::factory()->createOne());

    return $product->fresh();
}

it('edits the maintenance interval and the reminder switch on the product form', function (): void {
    $product = remindableProduct();

    livewire(EditProduct::class, ['record' => $product->id])
        ->fillForm([
            'maintenance_interval_months' => 6,
            'maintenance_reminders_enabled' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($product->fresh()->maintenance_interval_months)->toBe(6)
        ->and($product->fresh()->maintenance_reminders_enabled)->toBeFalse();
});

it('shows the next due date in the product table', function (): void {
    $product = remindableProduct();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords([$product])
        ->assertTableColumnStateSet('next_maintenance_due_date', '2026-03-10', $product);
});

it('sends a manual reminder from the product table', function (): void {
    $product = remindableProduct();

    livewire(ListProducts::class)
        ->callAction(TestAction::make('sendMaintenanceReminder')->table($product))
        ->assertNotified();

    Mail::assertQueued(MaintenanceReminderMail::class, 1);
    expect(MaintenanceReminder::query()->count())->toBe(1);
});

it('reports when a manual reminder cannot be sent', function (): void {
    $product = remindableProduct();
    $product->update(['warrantee_date' => '2020-01-01']);

    livewire(ListProducts::class)
        ->callAction(TestAction::make('sendMaintenanceReminder')->table($product))
        ->assertNotified();

    Mail::assertNothingQueued();
    expect(MaintenanceReminder::query()->count())->toBe(0);
});

it('edits the reminder switch on the user form', function (): void {
    $user = User::factory()->createOne();

    livewire(EditUser::class, ['record' => $user->id])
        ->fillForm(['maintenance_reminders_enabled' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->maintenance_reminders_enabled)->toBeFalse();
});
```

- [ ] **Step 2: Futtasd a tesztet, győződj meg róla, hogy bukik**

Run: `php artisan test --compact --filter=MaintenanceReminderProductActionsTest`
Expected: FAIL — a `maintenance_interval_months` mező nem létezik az űrlapon

- [ ] **Step 3: Bővítsd a készülék űrlapot**

`app/Filament/Resources/Products/Schemas/ProductFormSchema.php` — a `components([...])` tömb végére, a `Select::make('tool_id')` után:

```php
                Select::make('maintenance_interval_months')
                    ->label('Karbantartási ciklus')
                    ->options([
                        6 => 'Féléves',
                        12 => 'Éves',
                    ])
                    ->default(12)
                    ->required(),
                Toggle::make('maintenance_reminders_enabled')
                    ->label('Karbantartás emlékeztető küldése')
                    ->default(true),
```

Az importok közé: `use Filament\Forms\Components\Toggle;`

- [ ] **Step 4: Bővítsd a készülék táblát**

`app/Filament/Resources/Products/Tables/ProductTable.php` — az importok közé:

```php
use App\Models\Product;
use App\Services\MaintenanceReminderScheduler;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
```

A `columns([...])` tömbben a `TextColumn::make('tool.name')` után:

```php
                TextColumn::make('next_maintenance_due_date')
                    ->label('Következő esedékesség')
                    ->state(fn (Product $record): ?string => $record->nextMaintenanceDueDate()?->toDateString())
                    ->date(),
                IconColumn::make('maintenance_reminders_enabled')
                    ->label('Emlékeztető')
                    ->boolean(),
```

A `recordActions([...])` tömbbe, az `EditAction::make()` elé:

```php
                Action::make('sendMaintenanceReminder')
                    ->label('Emlékeztető küldése')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->requiresConfirmation()
                    ->modalHeading('Karbantartás emlékeztető küldése')
                    ->modalDescription('A készülékhez rendelt ügyfelek azonnal e-mailt kapnak.')
                    ->action(function (Product $record, MaintenanceReminderScheduler $scheduler): void {
                        $sent = $scheduler->sendManually($record);

                        if ($sent === 0) {
                            Notification::make()
                                ->warning()
                                ->title('Nem ment ki emlékeztető')
                                ->body('A készüléknek nincs értesíthető ügyfele, lejárt vagy hiányzik a garanciája, vagy ki van kapcsolva az emlékeztető.')
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->success()
                            ->title(sprintf('%d emlékeztető elküldve', $sent))
                            ->send();
                    }),
```

Az importok közé még: `use Filament\Support\Icons\Heroicon;`

- [ ] **Step 5: Bővítsd a felhasználó űrlapot**

`app/Filament/Resources/Users/Schemas/UserFormSchema.php` — a `components([...])` tömb végére:

```php
                Toggle::make('maintenance_reminders_enabled')
                    ->label('Karbantartás emlékeztető küldése')
                    ->default(true),
```

Az importok közé: `use Filament\Forms\Components\Toggle;`

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test --compact --filter=MaintenanceReminderProductActionsTest`
Expected: PASS (6 teszt)

- [ ] **Step 7: Futtasd a teljes tesztcsomagot**

Run: `php artisan test --compact`
Expected: PASS — nincs regresszió a meglévő teszteken (különösen a `ProductEditIntegrationTest` és a `ProductIndexTest` fájlokon, amelyek a most módosított űrlapot és táblát érintik).

- [ ] **Step 8: Formázás és commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/Products app/Filament/Resources/Users tests/Feature/Filament/MaintenanceReminderProductActionsTest.php
git commit -m "feat: manage maintenance reminders from the product and user forms"
```
