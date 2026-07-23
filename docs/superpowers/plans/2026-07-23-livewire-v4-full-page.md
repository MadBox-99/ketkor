# Livewire v4 full-page migráció — implementációs terv

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A `routes/web.php` controller-alapú Blade oldalait Livewire v4 full-page, class-based komponensekre állítjuk át, a `routes/auth.php` már meglévő mintája szerint.

**Architecture:** Doménenkénti függőleges szeletek. Minden szelet: komponens létrehozása → logika átköltöztetése → route átírása → nézet igazítása → controller-metódus törlése → teszt. A Filament admin panel változatlan marad. Az akció-route-ok (`add`, `remove`, `detach`, `productMove`, `removeUserFromOrganization`) komponens-metódussá válnak, így megszűnik három GET-en elérhető adatmódosítás.

**Tech Stack:** PHP 8.4.23, Laravel 13.21.1, Livewire v4.3, Filament v5.7, Pest v4, Tailwind v4.

**Spec:** `docs/superpowers/specs/2026-07-23-livewire-v4-full-page-design.md`

## Global Constraints

- **Branch:** `feature/livewire-v4-full-page`. Már létezik, ne hozz létre újat.
- **Komponens-generálás:** `php artisan make:livewire {Domén}/{Akció} --no-interaction`. A `config/livewire.php:107` már `'type' => 'class'`, tehát class-based komponens és külön Blade nézet készül. Ne használj `--sfc` / `--mfc` kapcsolót.
- **Layout:** minden full-page komponens osztályán `#[Layout('components.layouts.app')]`. Kivétel: az `Auth\*` komponensek, azokhoz nem nyúlunk.
- **Névtér:** `App\Livewire\{Domén}\{Akció}`. Nézet: `resources/views/livewire/{domén}/{akció}.blade.php`.
- **Route:** `Route::livewire('/út', Komponens::class)->name('...')`. A meglévő middleware-ek (`auth`, `verified`, `role:Organizer|Admin|Super Admin`) változatlanul maradnak a route-on.
- **Tesztfuttatás:** mindig `php artisan test`, **soha nem `--parallel`**. A `--parallel` módban 11 teszt bukik, de ez Laravel 12-n is így volt — nem a migráció okozza, ne próbáld javítani.
- **Formázás:** minden task végén, commit előtt `vendor/bin/pint --dirty`.
- **Tesztek törlése tilos** külön jóváhagyás nélkül (CLAUDE.md). A `tests/Feature/ToolSearchTest.php` és az `app/Livewire/ToolSearch.php` **marad a helyén**, noha halott kód.
- **Kiindulási zöld állapot:** 77 teszt, 161 assertion. Minden task végén ennyi vagy több legyen, és 0 bukó.
- **Commit-üzenet:** angol, conventional commit (`feat:`, `refactor:`, `chore:`).

---

### Task 1: Fázis 0 — halott kód törlése

Ez a legnagyobb egyszeri nyereség: a migrálandó felület felére csökken. Semmi nem költözik, csak törlünk. Minden itt törölt funkció megvan a Filament panelben.

**Files:**
- Delete: `app/Http/Controllers/LogController.php`
- Delete: `app/Http/Controllers/PartialController.php`
- Delete: `app/Http/Controllers/ProductLogController.php`
- Delete: `resources/views/log/index.blade.php` (és a `resources/views/log/` mappa)
- Delete: `resources/views/user/index.blade.php`, `create.blade.php`, `edit.blade.php` (és a `resources/views/user/` mappa)
- Modify: `app/Http/Controllers/ProfileController.php` — `index`, `create`, `store`, `show`, `userUpdate` metódusok törlése (23–101. sor környéke)
- Modify: `app/Http/Controllers/OrganizationController.php` — `show()` törlése
- Modify: `app/Http/Controllers/ProductController.php` — `show()` törlése
- Modify: `app/Http/Controllers/ToolController.php` — `show()` törlése
- Modify: `routes/web.php` — a `partials` és `productlogs` resource sorok törlése
- Test: `tests/Feature/DeadRoutesTest.php` (create)

**Interfaces:**
- Consumes: semmi.
- Produces: a `routes/web.php` már nem regisztrálja a `partials.*` és `productlogs.*` neveket. A `ProfileController` csak `edit`, `update`, `destroy` metódusokat tartalmaz.

**Miért biztonságos:** ellenőrizve, hogy `PartialController::store` és `ProductLogController::store` ugyanazt a logikát duplikálja, ami az `app/Livewire/ProductEdit.php`-ben él (partial létrehozás, garanciahosszabbítás), és egyik sincs hivatkozva egyetlen nézetből sem. A `LogController` és a `ProfileController` user-fele olyan route-nevekre hivatkozik (`logs.*`, `users.*`), amelyek soha nem voltak regisztrálva.

- [ ] **Step 1: Írd meg a bukó tesztet**

Hozd létre a `tests/Feature/DeadRoutesTest.php` fájlt:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('does not register routes for deleted controllers', function (string $name): void {
    expect(Route::has($name))->toBeFalse();
})->with([
    'partials.index',
    'partials.create',
    'partials.store',
    'partials.show',
    'partials.edit',
    'partials.update',
    'partials.destroy',
    'productlogs.index',
    'productlogs.create',
    'productlogs.store',
    'productlogs.show',
    'productlogs.edit',
    'productlogs.update',
    'productlogs.destroy',
    'organizations.show',
    'tools.show',
]);

it('does not ship controllers that have no routes', function (string $class): void {
    expect(class_exists($class))->toBeFalse();
})->with([
    'App\Http\Controllers\LogController',
    'App\Http\Controllers\PartialController',
    'App\Http\Controllers\ProductLogController',
]);
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/DeadRoutesTest.php`
Expected: FAIL — a `partials.*` és `productlogs.*` route-ok léteznek, és a három controller-osztály is létezik.

- [ ] **Step 3: Töröld a három halott controllert és a nézeteiket**

```bash
rm app/Http/Controllers/LogController.php
rm app/Http/Controllers/PartialController.php
rm app/Http/Controllers/ProductLogController.php
rm -r resources/views/log resources/views/user
```

- [ ] **Step 4: Töröld a `ProfileController` user-admin felét**

Az `app/Http/Controllers/ProfileController.php`-ból töröld az `index()`, `create()`, `store()`, `show()` és `userUpdate()` metódusokat. Csak az `edit()`, `update()` és `destroy()` maradjon. A metódusokkal együtt töröld a feleslegessé vált importokat is (`StoreUserRequest`, `UserUpdateRequest`, `Organization`, `Spatie\Permission\Models\Role` — amelyik már nincs használva).

- [ ] **Step 5: Töröld a három üres stub akciót**

- `app/Http/Controllers/OrganizationController.php` — `public function show(Organization $organization): void` metódus (a `//` törzsű) törlése.
- `app/Http/Controllers/ProductController.php` — `public function show(Product $product): void` törlése.
- `app/Http/Controllers/ToolController.php` — `public function show(Tool $tool): void` törlése.

- [ ] **Step 6: Vedd ki a halott resource route-okat**

A `routes/web.php`-ból töröld ezt a két sort:

```php
    Route::resource('partials', PartialController::class);
    Route::resource('productlogs', ProductLogController::class);
```

és a hozzájuk tartozó két `use` sort a fájl tetején:

```php
use App\Http\Controllers\PartialController;
use App\Http\Controllers\ProductLogController;
```

A `Route::resource('organizations', ...)` és `Route::resource('tools', ...)` sorokat cseréld explicit listára, hogy a törölt `show` ne regisztrálódjon:

```php
    Route::get('organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
    Route::get('organizations/{organization}/edit', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::put('organizations/{organization}', [OrganizationController::class, 'update'])->name('organizations.update');
    Route::delete('organizations/{organization}', [OrganizationController::class, 'destroy'])->name('organizations.destroy');

    Route::get('tools', [ToolController::class, 'index'])->name('tools.index');
    Route::get('tools/create', [ToolController::class, 'create'])->name('tools.create');
    Route::post('tools', [ToolController::class, 'store'])->name('tools.store');
    Route::get('tools/{tool}/edit', [ToolController::class, 'edit'])->name('tools.edit');
    Route::put('tools/{tool}', [ToolController::class, 'update'])->name('tools.update');
    Route::delete('tools/{tool}', [ToolController::class, 'destroy'])->name('tools.destroy');
```

- [ ] **Step 7: Futtasd a tesztet**

Run: `php artisan test tests/Feature/DeadRoutesTest.php`
Expected: PASS.

- [ ] **Step 8: Futtasd a teljes suite-ot**

Run: `php artisan test`
Expected: minden zöld, legalább 77 + az új dataset-esetek. Ha bármi bukik, az azt jelenti, hogy egy törölt osztályra vagy route-ra még van élő hivatkozás — keresd meg `grep`-pel és javítsd, ne állítsd vissza a törlést.

- [ ] **Step 9: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "chore: remove dead controllers, views and routes

LogController and the user-admin half of ProfileController referenced
route names (logs.*, users.*) that were never registered. PartialController
and ProductLogController consisted of 10 empty stubs plus a store() method
duplicating logic that already lives in App\Livewire\ProductEdit and was
unreachable from any view. All of this functionality exists in the Filament
panel."
```

---

### Task 2: Index oldal

A legkisebb szelet, ezzel hitelesítjük a mintát a többi task előtt.

**Files:**
- Create: `app/Livewire/Index.php`
- Create: `resources/views/livewire/index.blade.php`
- Delete: `resources/views/index.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/IndexPageTest.php` (create)

**Interfaces:**
- Consumes: a Task 1 utáni `routes/web.php`.
- Produces: `App\Livewire\Index` — full-page komponens, `index` route-néven elérhető. Ez a minta, amit a Task 3–12 követ.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/IndexPageTest.php`:

```php
<?php

declare(strict_types=1);

use App\Livewire\Index;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('renders the index page as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/IndexPageTest.php`
Expected: FAIL — `Class "App\Livewire\Index" not found`.

- [ ] **Step 3: Generáld a komponenst**

Run: `php artisan make:livewire Index --no-interaction`

Ez létrehozza az `app/Livewire/Index.php`-t és a `resources/views/livewire/index.blade.php`-t.

- [ ] **Step 4: Írd meg a komponenst**

`app/Livewire/Index.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component
{
    public function render(): Factory|View
    {
        return view('livewire.index');
    }
}
```

- [ ] **Step 5: Írd meg a nézetet**

`resources/views/livewire/index.blade.php` — a törzs a régi `resources/views/index.blade.php`-ből jön, de az `<x-layouts.app>` burkoló **nélkül**, mert azt már a `#[Layout]` attribútum adja. A Livewire komponensnek egyetlen gyökérelemre van szüksége:

```blade
<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('index') }}
        </h2>
    </x-slot>
</div>
```

- [ ] **Step 6: Írd át a route-ot**

A `routes/web.php`-ban cseréld ezt:

```php
    Route::get('/', fn (): Factory|View => view('index'))->name('index');
```

erre:

```php
    Route::livewire('/', Index::class)->name('index');
```

Add hozzá az `use App\Livewire\Index;` importot, és töröld a feleslegessé vált `use Illuminate\Contracts\View\Factory;` / `use Illuminate\Contracts\View\View;` sorokat, ha már semmi nem használja őket a fájlban.

- [ ] **Step 7: Töröld a régi nézetet**

```bash
rm resources/views/index.blade.php
```

- [ ] **Step 8: Futtasd a teszteket**

Run: `php artisan test tests/Feature/IndexPageTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 9: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert index route to a full-page Livewire component"
```

---

### Task 3: Products\Search — a products.add beolvasztásával

**Files:**
- Create: `app/Livewire/Products/Search.php` (a meglévő `app/Livewire/ProductSearch.php` áthelyezésével)
- Create: `resources/views/livewire/products/search.blade.php`
- Delete: `app/Livewire/ProductSearch.php`, `resources/views/livewire/product-search.blade.php`, `resources/views/product/search.blade.php`
- Modify: `routes/web.php`, `app/Http/Controllers/ProductController.php`
- Modify: `tests/Feature/ProductSearchTest.php`

**Interfaces:**
- Consumes: `App\Livewire\Index` mintája (Task 2).
- Produces: `App\Livewire\Products\Search` a következő publikus felülettel:
  - `public string $serial_number` — `#[Validate('required|min:3|max:255')]`
  - `public ?Product $product`
  - `public bool $owns`
  - `public function find(): void` — megkeresi a terméket sorozatszám alapján
  - `public function addToMyProducts(): void` — a bejelentkezett felhasználóhoz rendeli a talált terméket, majd átirányít a `products.edit`-re

- [ ] **Step 1: Írd át a meglévő tesztet és bővítsd**

A `tests/Feature/ProductSearchTest.php`-ban cseréld a `ProductSearch` hivatkozásokat `Products\Search`-re. Az **állításokat ne változtasd** — ez a bizonyíték, hogy a viselkedés nem tört el. A fájl elején:

```php
use App\Livewire\Products\Search;
```

és minden `Livewire::test(ProductSearch::class)` → `Livewire::test(Search::class)`.

Add hozzá ezt a két új tesztet a fájl végére:

```php
it('renders as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('products.search'))
        ->assertOk()
        ->assertSeeLivewire(Search::class);
});

it('attaches the found product to the current user', function (): void {
    $user = User::factory()->createOne();
    $product = Product::factory()->createOne();

    actingAs($user);

    Livewire::test(Search::class)
        ->set('serial_number', $product->serial_number)
        ->call('find')
        ->call('addToMyProducts')
        ->assertRedirect(route('products.edit', ['product' => $product]));

    expect($user->fresh()->products)->toHaveCount(1);
});

it('no longer exposes a GET route for attaching products', function (): void {
    expect(Route::has('products.add'))->toBeFalse();
});
```

A szükséges importok a fájl tetején: `use Illuminate\Support\Facades\Route;`, `use function Pest\Laravel\get;`.

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/ProductSearchTest.php`
Expected: FAIL — `Class "App\Livewire\Products\Search" not found`.

- [ ] **Step 3: Hozd létre a komponenst**

`app/Livewire/Products/Search.php`. A tartalom a mai `app/Livewire/ProductSearch.php`-ból jön, három változtatással: új névtér, `#[Layout]` attribútum, és a `find()` már nem ad vissza nézetet (a Livewire akció-metódusnak nem kell), plusz az új `addToMyProducts()`.

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Search extends Component
{
    #[Validate('required|min:3|max:255')]
    public string $serial_number = '';

    public ?Product $product = null;

    public bool $owns = false;

    public function find(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate();

        $product = Product::query()
            ->whereSerialNumber($this->serial_number)
            ->first();

        $this->product = $product;
        $this->owns = $product instanceof Product
            && $product->users->where('id', $user->id)->isNotEmpty();
    }

    public function addToMyProducts(): void
    {
        if (! $this->product instanceof Product) {
            return;
        }

        /** @var User $user */
        $user = Auth::user();
        $user->products()->attach($this->product->id);

        $this->redirectRoute('products.edit', ['product' => $this->product], navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.products.search', ['product' => $this->product]);
    }
}
```

- [ ] **Step 4: Hozd létre a nézetet**

`resources/views/livewire/products/search.blade.php`. A tartalom a mai `resources/views/livewire/product-search.blade.php`-ból jön, kiegészítve a `resources/views/product/search.blade.php` fejlécével, mert az most a komponensbe költözik:

```blade
<div>
    <x-slot name="header">
        <div class="mb-4 flex items-center justify-between font-bold">
            <div class="flex-auto">
                <h1 class="mx-1 px-2 text-3xl text-primary sm:text-2xl md:text-xl">{{ __('Search product') }}</h1>
            </div>
        </div>
    </x-slot>

    <x-alert />

    <div class="my-8 w-full overflow-hidden shadow-sm">
        {{-- ide másold a régi resources/views/livewire/product-search.blade.php gyökérelemének TELJES belsejét --}}
    </div>
</div>
```

**Fontos:** a régi nézetben minden `route('products.add', ...)` linket cserélj `wire:click="addToMyProducts"` gombra, mert a route megszűnik.

- [ ] **Step 5: Írd át a route-ot**

A `routes/web.php` `products` prefix csoportjában:

```php
        Route::livewire('/search', Search::class)->name('search');
```

és **töröld** ezt a sort:

```php
        Route::get('/add/{product}', [ProductController::class, 'add'])->name('add');
```

Import: `use App\Livewire\Products\Search;`

- [ ] **Step 6: Töröld a régi fájlokat és a controller-metódusokat**

```bash
rm app/Livewire/ProductSearch.php
rm resources/views/livewire/product-search.blade.php
rm resources/views/product/search.blade.php
```

Az `app/Http/Controllers/ProductController.php`-ból töröld a `search()` és az `add()` metódust.

- [ ] **Step 7: Futtasd a teszteket**

Run: `php artisan test tests/Feature/ProductSearchTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 8: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert product search to a full-page Livewire component

Absorbs the products.add route into the component as a wire:click action,
removing a data-mutating GET endpoint that had no CSRF protection."
```

---

### Task 4: Products\MyProducts — a products.remove törlésével

**Files:**
- Create: `app/Livewire/Products/MyProducts.php` (a `app/Livewire/ProductSearchUser.php` áthelyezésével)
- Create: `resources/views/livewire/products/my-products.blade.php`
- Delete: `app/Livewire/ProductSearchUser.php`, `resources/views/livewire/product-search-user.blade.php`, `resources/views/product/myproduct.blade.php`
- Modify: `routes/web.php`, `app/Http/Controllers/ProductController.php`
- Modify: `tests/Feature/ProductSearchUserTest.php`

**Interfaces:**
- Consumes: a Task 3-ban rögzített minta.
- Produces: `App\Livewire\Products\MyProducts` — `HasActions`, `HasSchemas`, `HasTable` interfészeket implementálja, `public function table(Table $table): Table` és `protected function getTableQuery(): Builder` metódusokkal. Ez a minta a Task 7, 8, 9 Filament Table komponenseihez.

**Megjegyzés:** a `products.remove` route törölhető, mert a komponens `delete` record action-je (`app/Livewire/ProductSearchUser.php:136-148`) pontosan ugyanezt csinálja. A `ProductController::remove()` Filament `Notification`-t is küld — ezt tedd bele a record action-be, hogy a felhasználói visszajelzés ne vesszen el.

- [ ] **Step 1: Írd át a meglévő tesztet és bővítsd**

A `tests/Feature/ProductSearchUserTest.php`-ban `ProductSearchUser` → `MyProducts`, import: `use App\Livewire\Products\MyProducts;`. Az állítások változatlanok.

Új tesztek a fájl végére:

```php
it('renders as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('products.myproducts'))
        ->assertOk()
        ->assertSeeLivewire(MyProducts::class);
});

it('no longer exposes a separate remove route', function (): void {
    expect(Route::has('products.remove'))->toBeFalse();
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/ProductSearchUserTest.php`
Expected: FAIL — `Class "App\Livewire\Products\MyProducts" not found`.

- [ ] **Step 3: Hozd létre a komponenst**

`app/Livewire/Products/MyProducts.php`. Másold át az `app/Livewire/ProductSearchUser.php` **teljes tartalmát** változatlanul, ezekkel a módosításokkal:

- `namespace App\Livewire\Products;`
- osztálynév: `MyProducts`
- `#[Layout('components.layouts.app')]` attribútum az osztály fölé, `use Livewire\Attributes\Layout;` importtal
- a `render()` metódus `view('livewire.products.my-products')`-ot adjon vissza
- a `delete` record action `->action(...)` closure-jébe a detach után kerüljön be a Filament értesítés:

```php
                    ->action(function (Product $record): void {
                        /** @var User $user */
                        $user = Auth::user();
                        $record->users()->detach($user->id);

                        Notification::make()
                            ->title(__('Succesfuly removed the product from your account.'))
                            ->success()
                            ->send();
                    }),
```

Import: `use Filament\Notifications\Notification;`

- [ ] **Step 4: Hozd létre a nézetet**

`resources/views/livewire/products/my-products.blade.php` — a mai `resources/views/livewire/product-search-user.blade.php` tartalma, a `resources/views/product/myproduct.blade.php` fejlécével kiegészítve:

```blade
<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('My products') }}
            </h2>
        </div>
    </x-slot>

    <x-alert />

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            {{-- ide másold a régi product-search-user.blade.php gyökérelemének TELJES belsejét --}}
        </div>
    </div>
</div>
```

- [ ] **Step 5: Írd át a route-ot**

```php
        Route::livewire('/myproducts', MyProducts::class)->name('myproducts');
```

**Töröld** ezt a sort:

```php
        Route::delete('/remove/{product}', [ProductController::class, 'remove'])->name('remove');
```

Import: `use App\Livewire\Products\MyProducts;`

- [ ] **Step 6: Töröld a régi fájlokat és a controller-metódusokat**

```bash
rm app/Livewire/ProductSearchUser.php
rm resources/views/livewire/product-search-user.blade.php
rm resources/views/product/myproduct.blade.php
```

Az `app/Http/Controllers/ProductController.php`-ból töröld a `myproducts()` és a `remove()` metódust.

- [ ] **Step 7: Futtasd a teszteket**

Run: `php artisan test tests/Feature/ProductSearchUserTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 8: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert my-products to a full-page Livewire component

Drops the products.remove route; the table's delete record action already
performed the same detach, now with the notification the controller sent."
```

---

### Task 5: Products\Edit

**Files:**
- Create: `app/Livewire/Products/Edit.php` (a `app/Livewire/ProductEdit.php` áthelyezésével)
- Create: `resources/views/livewire/products/edit.blade.php`
- Delete: `app/Livewire/ProductEdit.php`, `resources/views/livewire/product-edit.blade.php`, `resources/views/product/edit.blade.php`
- Delete: `app/Http/Controllers/ProductController.php` (az utolsó két metódusa is elfogy)
- Modify: `routes/web.php`
- Modify: `tests/Feature/ProductEditIntegrationTest.php`, `tests/Feature/ProductEditEventValidationTest.php`

**Interfaces:**
- Consumes: a Task 3–4 mintája.
- Produces: `App\Livewire\Products\Edit` — `public Product $product` property-vel, route model bindingből. Ez a Task 6 refaktorálásának bemenete.

**Fontos:** ez a task **csak áthelyez**, nem szed szét. A `ProductEdit` 572 soros; a szétszedése a Task 6, külön commitban, hogy a két változás külön legyen visszagörgethető.

- [ ] **Step 1: Írd át a két meglévő tesztet**

Mindkét fájlban (`tests/Feature/ProductEditIntegrationTest.php`, `tests/Feature/ProductEditEventValidationTest.php`) `ProductEdit` → `Edit`, import: `use App\Livewire\Products\Edit;`. **Az állítások változatlanok.**

Új teszt a `ProductEditIntegrationTest.php` végére:

```php
it('renders as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());
    $product = Product::factory()->createOne();

    get(route('products.edit', ['product' => $product]))
        ->assertOk()
        ->assertSeeLivewire(Edit::class);
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/ProductEditIntegrationTest.php tests/Feature/ProductEditEventValidationTest.php`
Expected: FAIL — `Class "App\Livewire\Products\Edit" not found`.

- [ ] **Step 3: Helyezd át a komponenst**

```bash
git mv app/Livewire/ProductEdit.php app/Livewire/Products/Edit.php
```

Az `app/Livewire/Products/Edit.php`-ban:
- `namespace App\Livewire\Products;`
- osztálynév: `Edit`
- `#[Layout('components.layouts.app')]` az osztály fölé, `use Livewire\Attributes\Layout;` importtal
- a `render()` adjon vissza `view('livewire.products.edit')`-et
- a `mount()` metódusban töltsd be azt, amit eddig a `ProductController::edit()` adott át a nézetnek:

```php
    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->partials = Partial::query()
            ->where('product_id', $product->id)
            ->latest()
            ->limit(6)
            ->get();
        $this->users = User::query()->orderBy('name')->get();
        $this->tools = Tool::query()->orderBy('name')->get();
    }
```

Ha a komponensnek már van `mount()`-ja, egészítsd ki, ne írd felül. A `$partials`, `$users`, `$tools` property-ket vedd fel, ha még nincsenek, `#[Locked]` nélkül, publikusként.

- [ ] **Step 4: Helyezd át a nézetet**

```bash
git mv resources/views/livewire/product-edit.blade.php resources/views/livewire/products/edit.blade.php
```

Illeszd bele a `resources/views/product/edit.blade.php` fejlécét a gyökérelem elejére:

```blade
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ __('Edit product') }}
            </h1>

            <x-button-style-link text="Edit product" route="products.myproducts">
                {{ __('Back') }}
            </x-button-style-link>
        </div>
    </x-slot>

    <x-alert />
```

- [ ] **Step 5: Írd át a route-ot és töröld a controllert**

```php
        Route::livewire('/edit/{product}', Edit::class)->name('edit');
```

**Töröld** a `products.update` route-ot — a frissítést a komponens végzi:

```php
        Route::put('/update/{product}', [ProductController::class, 'update'])->name('update');
```

Ekkor a `ProductController` üresre fogyott:

```bash
rm app/Http/Controllers/ProductController.php
rm resources/views/product/edit.blade.php
```

Töröld a `use App\Http\Controllers\ProductController;` importot a `routes/web.php`-ból, és add hozzá az `use App\Livewire\Products\Edit;`-et.

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test tests/Feature/ProductEditIntegrationTest.php tests/Feature/ProductEditEventValidationTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 7: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert product edit to a full-page Livewire component

ProductController is now empty and removed. The data the controller loaded
for the view (partials, users, tools) moves into the component's mount()."
```

---

### Task 6: A Products\Edit szétszedése

572 sor egy komponensben túl sok. A szétszedés önálló commit, hogy külön visszagörgethető legyen a route-váltástól.

**Files:**
- Create: `app/Livewire/Products/Concerns/BuildsProductSchemas.php`
- Create: `app/Livewire/Products/Support/MaintenanceWindow.php`
- Modify: `app/Livewire/Products/Edit.php`
- Test: `tests/Unit/MaintenanceWindowTest.php` (create)

**Interfaces:**
- Consumes: `App\Livewire\Products\Edit` (Task 5).
- Produces:
  - `App\Livewire\Products\Support\MaintenanceWindow` — `public static function allows(Product $product, CarbonInterface $now): bool` és `public static function nextWindow(Product $product): array{start: CarbonInterface, end: CarbonInterface}`. Tiszta, Livewire-független osztály, ezért unit-tesztelhető.
  - `App\Livewire\Products\Concerns\BuildsProductSchemas` — trait, ami a `productForm()` és az esemény-űrlap schema-metódusokat tartalmazza.

**Kritikus szabály:** a `ProductEditEventValidationTest` és a `ProductEditIntegrationTest` **állításai egyetlen karakterrel sem változhatnak** ebben a taskban. Ha bármelyiket módosítani kell, az azt jelenti, hogy a refaktorálás viselkedést változtatott — állj meg és gondold újra.

- [ ] **Step 1: Írd meg a unit tesztet az új osztályra**

`tests/Unit/MaintenanceWindowTest.php`:

```php
<?php

declare(strict_types=1);

use App\Livewire\Products\Support\MaintenanceWindow;
use App\Models\Product;
use Illuminate\Support\Facades\Date;

it('allows maintenance inside the warranty window', function (): void {
    $product = Product::factory()->makeOne(['warrantee_date' => Date::parse('2026-01-01')]);

    expect(MaintenanceWindow::allows($product, Date::parse('2025-12-15')))->toBeTrue()
        ->and(MaintenanceWindow::allows($product, Date::parse('2026-02-15')))->toBeTrue();
});

it('rejects maintenance outside the warranty window', function (): void {
    $product = Product::factory()->makeOne(['warrantee_date' => Date::parse('2026-01-01')]);

    expect(MaintenanceWindow::allows($product, Date::parse('2025-11-01')))->toBeFalse()
        ->and(MaintenanceWindow::allows($product, Date::parse('2026-04-01')))->toBeFalse();
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Unit/MaintenanceWindowTest.php`
Expected: FAIL — `Class "App\Livewire\Products\Support\MaintenanceWindow" not found`.

- [ ] **Step 3: Emeld ki a MaintenanceWindow osztályt**

Az `app/Livewire/Products/Edit.php` `validateMaintenanceTiming()` metódusából (a mai `ProductEdit.php:386` környéke) és a körülötte lévő dátumszámításokból (`:317-330`) emeld ki a tiszta logikát:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Products\Support;

use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

final class MaintenanceWindow
{
    /**
     * @return array{start: CarbonInterface, end: CarbonInterface}
     */
    public static function nextWindow(Product $product): array
    {
        $warranteeDate = Date::parse($product->serializeDate($product->warrantee_date));

        return [
            'start' => $warranteeDate->copy()->subMonth(),
            'end' => $warranteeDate->copy()->addMonths(2),
        ];
    }

    public static function allows(Product $product, CarbonInterface $now): bool
    {
        $window = self::nextWindow($product);

        return $now->between($window['start'], $window['end']);
    }
}
```

Az `Edit.php`-ban a `validateMaintenanceTiming()` hívja ezt, a többi logikája (a 11–13 hónapos szabály a második karbantartásra) maradjon a komponensben, mert az a komponens állapotára támaszkodik.

- [ ] **Step 4: Emeld ki a schema-építő traitet**

Az `app/Livewire/Products/Edit.php`-ból mozgasd át a `productForm()` metódust és a hozzá tartozó privát schema-építő metódusokat az `app/Livewire/Products/Concerns/BuildsProductSchemas.php` traitbe:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Products\Concerns;

use Filament\Schemas\Schema;

trait BuildsProductSchemas
{
    // ide kerül a productForm() és a további schema-metódusok,
    // változatlan törzzsel
}
```

Az `Edit` osztályban: `use BuildsProductSchemas;`

- [ ] **Step 5: Futtasd a teszteket**

Run: `php artisan test tests/Unit/MaintenanceWindowTest.php`
Expected: PASS.

Run: `php artisan test tests/Feature/ProductEditIntegrationTest.php tests/Feature/ProductEditEventValidationTest.php`
Expected: PASS, **változatlan állításokkal**.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 6: Ellenőrizd a méretet**

Run: `wc -l app/Livewire/Products/Edit.php`
Expected: érdemben 572 alatt. Ha nem csökkent legalább 150 sorral, nézd meg, mi maradt még kiemelhető.

- [ ] **Step 7: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "refactor: split Products\\Edit into focused units

Extracts the warranty-window arithmetic into a Livewire-independent
MaintenanceWindow class (now unit-tested) and the Filament schema builders
into a trait. Existing feature test assertions are unchanged, which is the
evidence that behaviour did not shift."
```

---

### Task 7: Products\Index — új Filament Table

Ez a route jelenleg **hibát dob**, mert a `resources/views/product/index.blade.php:7` a nem létező `product-filament-table` komponensre hivatkozik.

**Files:**
- Create: `app/Livewire/Products/Index.php`
- Create: `resources/views/livewire/products/index.blade.php`
- Delete: `resources/views/product/index.blade.php` (és a `resources/views/product/` mappa, ha kiürült)
- Modify: `routes/web.php`
- Test: `tests/Feature/ProductIndexTest.php` (create)

**Interfaces:**
- Consumes: a `Products\MyProducts` Filament Table mintája (Task 4).
- Produces: `App\Livewire\Products\Index`.

**Oszlopdefiníció:** ne találj ki újat — emeld át az `app/Filament/Resources/Products/Tables/ProductTable.php`-ból (vagy ahogy a fájl neve van; nézd meg az `app/Filament/Resources/Products/` mappát).

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/ProductIndexTest.php`:

```php
<?php

declare(strict_types=1);

use App\Livewire\Products\Index;
use App\Models\Product;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders as a full-page livewire component', function (): void {
    actingAs(User::factory()->createOne());

    get(route('products.index'))
        ->assertOk()
        ->assertSeeLivewire(Index::class);
});

it('lists products in the table', function (): void {
    actingAs(User::factory()->createOne());
    $products = Product::factory()->count(3)->create();

    livewire(Index::class)
        ->assertCanSeeTableRecords($products);
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/ProductIndexTest.php`
Expected: FAIL — `Class "App\Livewire\Products\Index" not found`.

- [ ] **Step 3: Nézd meg a Filament tábladefiníciót**

Run: `ls app/Filament/Resources/Products/ app/Filament/Resources/Products/Tables/`

Olvasd el a tábla-osztályt; az oszlopokat innen emeld át.

- [ ] **Step 4: Írd meg a komponenst**

`app/Livewire/Products/Index.php` — a `MyProducts` szerkezetét kövesd, de a query az összes terméket adja:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Products;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Index extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                // ide az app/Filament/Resources/Products/ tábladefiníciójából átemelt oszlopok
            ])
            ->recordActions([
                Action::make('edit')
                    ->label(__('Edit'))
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->url(fn (Product $record): string => route('products.edit', ['product' => $record->id])),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        return Product::query();
    }

    public function render(): Factory|View
    {
        return view('livewire.products.index');
    }
}
```

- [ ] **Step 5: Írd meg a nézetet**

`resources/views/livewire/products/index.blade.php`:

```blade
<div>
    <x-alert />

    <div class="py-12">
        <div class="mx-auto max-w-full space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                {{ $this->table }}
            </div>
        </div>
    </div>
</div>
```

- [ ] **Step 6: Írd át a route-ot**

```php
        Route::livewire('/', Index::class)->name('index');
```

Import: `use App\Livewire\Products\Index as ProductIndex;` — figyelj a névütközésre az `App\Livewire\Index`-szel (Task 2). Használj aliast, és a route-ban is az aliast add meg.

- [ ] **Step 7: Töröld a régi nézetet**

```bash
rm resources/views/product/index.blade.php
rmdir resources/views/product 2>/dev/null || true
```

- [ ] **Step 8: Futtasd a teszteket**

Run: `php artisan test tests/Feature/ProductIndexTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 9: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: add Products\\Index full-page table component

Replaces the products.index route, which previously rendered a view
referencing a Livewire component (product-filament-table) that did not exist."
```

---

### Task 8: Tools domén

**Files:**
- Create: `app/Livewire/Tools/Index.php`, `Create.php`, `Edit.php`
- Create: `resources/views/livewire/tools/index.blade.php`, `create.blade.php`, `edit.blade.php`
- Delete: `app/Http/Controllers/ToolController.php`, `resources/views/tool/` (teljes mappa)
- Modify: `routes/web.php`
- Test: `tests/Feature/ToolPagesTest.php` (create)

**Interfaces:**
- Consumes: a `Products\Index` Filament Table mintája (Task 7).
- Produces: `App\Livewire\Tools\Index`, `App\Livewire\Tools\Create`, `App\Livewire\Tools\Edit`.

**Validációs szabályok** — pontosan a `ToolController::store()`-ból (`app/Http/Controllers/ToolController.php:41-46`):

```
'name' => ['required', 'string'],
'category' => ['nullable', 'string'],
'tag' => ['nullable', 'string'],
'factory_name' => ['nullable', 'string'],
```

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/ToolPagesTest.php`:

```php
<?php

declare(strict_types=1);

use App\Livewire\Tools\Create;
use App\Livewire\Tools\Edit;
use App\Livewire\Tools\Index;
use App\Models\Tool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->createOne());
});

it('renders the tools index', function (): void {
    get(route('tools.index'))->assertOk()->assertSeeLivewire(Index::class);
});

it('lists tools in the table', function (): void {
    $tools = Tool::factory()->count(3)->create();

    livewire(Index::class)->assertCanSeeTableRecords($tools);
});

it('renders the create page', function (): void {
    get(route('tools.create'))->assertOk()->assertSeeLivewire(Create::class);
});

it('creates a tool', function (): void {
    livewire(Create::class)
        ->set('name', 'Drill')
        ->set('category', 'power')
        ->call('save')
        ->assertRedirect(route('tools.index'));

    assertDatabaseHas(Tool::class, ['name' => 'Drill', 'category' => 'power']);
});

it('requires a name when creating a tool', function (): void {
    livewire(Create::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

it('renders the edit page', function (): void {
    $tool = Tool::factory()->createOne();

    get(route('tools.edit', ['tool' => $tool]))->assertOk()->assertSeeLivewire(Edit::class);
});

it('updates a tool', function (): void {
    $tool = Tool::factory()->createOne(['name' => 'Old']);

    livewire(Edit::class, ['tool' => $tool])
        ->set('name', 'New')
        ->call('save')
        ->assertRedirect(route('tools.index'));

    expect($tool->fresh()->name)->toBe('New');
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/ToolPagesTest.php`
Expected: FAIL — a `App\Livewire\Tools\*` osztályok nem léteznek.

- [ ] **Step 3: Generáld a három komponenst**

```bash
php artisan make:livewire Tools/Index --no-interaction
php artisan make:livewire Tools/Create --no-interaction
php artisan make:livewire Tools/Edit --no-interaction
```

- [ ] **Step 4: Írd meg a Create komponenst**

`app/Livewire/Tools/Create.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Tools;

use App\Models\Tool;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.app')]
class Create extends Component
{
    #[Validate('required|string')]
    public string $name = '';

    #[Validate('nullable|string')]
    public ?string $category = null;

    #[Validate('nullable|string')]
    public ?string $tag = null;

    #[Validate('nullable|string')]
    public ?string $factory_name = null;

    public function save(): void
    {
        $validated = $this->validate();

        DB::beginTransaction();

        try {
            Tool::query()->create($validated);
            DB::commit();
        } catch (Throwable $throwable) {
            DB::rollback();

            session()->flash('error', $throwable->getMessage());

            return;
        }

        session()->flash('success', __('Tool created successfully.'));

        $this->redirectRoute('tools.index', navigate: true);
    }

    public function render(): Factory|View
    {
        return view('livewire.tools.create');
    }
}
```

- [ ] **Step 5: Írd meg az Edit komponenst**

`app/Livewire/Tools/Edit.php` — ugyanez, de `mount(Tool $tool)`-lal tölti fel a property-ket, a `save()` pedig `$this->tool->update($validated)`-et hív, és `__('Tool updated successfully.')` üzenetet villant. A `public Tool $tool;` property-t vedd fel.

- [ ] **Step 6: Írd meg az Index komponenst**

`app/Livewire/Tools/Index.php` — a `Products\Index` (Task 7) szerkezetét kövesd. A query `Tool::query()`, az oszlopokat az `app/Filament/Resources/Tools/` tábladefiníciójából emeld át. A record action-ök: `edit` (URL a `tools.edit`-re) és `delete`:

```php
                Action::make('delete')
                    ->label(__('Delete'))
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Tool $record): void {
                        $record->delete();

                        Notification::make()
                            ->title(__('Tool deleted successfully.'))
                            ->success()
                            ->send();
                    }),
```

- [ ] **Step 7: Írd meg a három nézetet**

Mindhárom a régi `resources/views/tool/*.blade.php` tartalmát viszi tovább, `<x-layouts.app>` burkoló nélkül, egyetlen `<div>` gyökérelemmel. Az űrlapok `<form method="POST" action="{{ route('tools.store') }}">` helyett `wire:submit="save"`-et használnak, a mezők pedig `wire:model`-t.

- [ ] **Step 8: Írd át a route-okat és töröld a controllert**

A Task 1-ben felvett explicit `tools.*` route-listát cseréld erre:

```php
    Route::livewire('tools', Tools\Index::class)->name('tools.index');
    Route::livewire('tools/create', Tools\Create::class)->name('tools.create');
    Route::livewire('tools/{tool}/edit', Tools\Edit::class)->name('tools.edit');
```

A `tools.store`, `tools.update`, `tools.destroy` route-ok megszűnnek — a komponensek végzik.

```bash
rm app/Http/Controllers/ToolController.php
rm -r resources/views/tool
```

- [ ] **Step 9: Futtasd a teszteket**

Run: `php artisan test tests/Feature/ToolPagesTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 10: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert the tools domain to full-page Livewire components

ToolController is removed. tools.index previously rendered a view
referencing a Livewire component (tools-table) that did not exist."
```

---

### Task 9: Organizations\Index és a beágyazott UsersTable

**Files:**
- Create: `app/Livewire/Organizations/Index.php`, `app/Livewire/Organizations/UsersTable.php`
- Create: `resources/views/livewire/organizations/index.blade.php`, `users-table.blade.php`
- Delete: `resources/views/organization/index.blade.php`
- Modify: `resources/views/organization/edit.blade.php:45`, `routes/web.php`
- Test: `tests/Feature/OrganizationIndexTest.php` (create)

**Interfaces:**
- Consumes: a `Products\Index` Filament Table mintája (Task 7).
- Produces:
  - `App\Livewire\Organizations\Index` — full-page.
  - `App\Livewire\Organizations\UsersTable` — **beágyazott** komponens, `public int $organization` paraméterrel (a mai `resources/views/organization/edit.blade.php:45` `:organization="$organization->id"`-t ad át). Nincs rajta `#[Layout]`.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/OrganizationIndexTest.php`:

```php
<?php

declare(strict_types=1);

use App\Livewire\Organizations\Index;
use App\Livewire\Organizations\UsersTable;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    actingAs(User::factory()->createOne());
});

it('renders the organizations index', function (): void {
    get(route('organizations.index'))->assertOk()->assertSeeLivewire(Index::class);
});

it('lists organizations in the table', function (): void {
    $organizations = Organization::factory()->count(3)->create();

    livewire(Index::class)->assertCanSeeTableRecords($organizations);
});

it('lists only the given organization members in the users table', function (): void {
    $organization = Organization::factory()->createOne();
    $member = User::factory()->createOne(['organization_id' => $organization->id]);
    $outsider = User::factory()->createOne();

    livewire(UsersTable::class, ['organization' => $organization->id])
        ->assertCanSeeTableRecords([$member])
        ->assertCanNotSeeTableRecords([$outsider]);
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/OrganizationIndexTest.php`
Expected: FAIL — az osztályok nem léteznek.

- [ ] **Step 3: Generáld a komponenseket**

```bash
php artisan make:livewire Organizations/Index --no-interaction
php artisan make:livewire Organizations/UsersTable --no-interaction
```

- [ ] **Step 4: Írd meg az Index komponenst**

A `Products\Index` (Task 7) szerkezete, `Organization::query()` query-vel. Az oszlopokat az `app/Filament/Resources/Organizations/` tábladefiníciójából emeld át. Record action-ök: `edit` (`organizations.edit`) és `delete`.

- [ ] **Step 5: Írd meg a UsersTable komponenst**

`app/Livewire/Organizations/UsersTable.php` — **`#[Layout]` nélkül**, mert beágyazott:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Organizations;

use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class UsersTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public int $organization;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')->label(__('Name'))->searchable()->sortable(),
                TextColumn::make('email')->label(__('Email'))->searchable(),
            ])
            ->paginated([10, 25, 50]);
    }

    protected function getTableQuery(): Builder
    {
        return User::query()->where('organization_id', $this->organization);
    }

    public function render(): Factory|View
    {
        return view('livewire.organizations.users-table');
    }
}
```

- [ ] **Step 6: Írd meg a nézeteket**

`resources/views/livewire/organizations/users-table.blade.php`:

```blade
<div>
    {{ $this->table }}
</div>
```

`resources/views/livewire/organizations/index.blade.php` — a régi `resources/views/organization/index.blade.php` tartalma, `<x-layouts.app>` nélkül, a `@livewire('organizations-table')` helyén `{{ $this->table }}`-lel.

- [ ] **Step 7: Javítsd a törött hivatkozást az edit nézetben**

A `resources/views/organization/edit.blade.php:45`-ben ez a sor most nem létező komponensre mutat:

```blade
<livewire:organization-details-users-table :organization="$organization->id" />
```

Cseréld erre:

```blade
<livewire:organizations.users-table :organization="$organization->id" />
```

- [ ] **Step 8: Írd át a route-ot**

```php
    Route::livewire('organizations', Organizations\Index::class)->name('organizations.index');
```

- [ ] **Step 9: Futtasd a teszteket**

Run: `php artisan test tests/Feature/OrganizationIndexTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 10: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: add Organizations\\Index and UsersTable components

Both replace references to Livewire components that never existed
(organizations-table, organization-details-users-table)."
```

---

### Task 10: Organizations\Create és Edit

**Files:**
- Create: `app/Livewire/Organizations/Create.php`, `Edit.php`
- Create: `resources/views/livewire/organizations/create.blade.php`, `edit.blade.php`
- Delete: `resources/views/organization/create.blade.php`, `edit.blade.php`
- Modify: `routes/web.php`, `app/Http/Controllers/OrganizationController.php`
- Test: `tests/Feature/OrganizationFormsTest.php` (create)

**Interfaces:**
- Consumes: a `Tools\Create` / `Tools\Edit` mintája (Task 8).
- Produces: `App\Livewire\Organizations\Create`, `App\Livewire\Organizations\Edit`.

**Validációs szabályok** — pontosan az `OrganizationController::store()`-ból:

```
'name' => ['required', 'string'],
'city' => ['string'],
'address' => ['string'],
'zip' => ['string'],
'tax_number' => ['required', 'max:24'],
```

**Fontos mellékhatás:** a `store()` a szervezet létrehozása után a bejelentkezett felhasználó `organization_id`-ját is beállítja (`app/Http/Controllers/OrganizationController.php:60-62`). Ezt a `Create::save()`-nek is meg kell tennie, különben a `myorganization` oldal nem találja meg a szervezetet.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/OrganizationFormsTest.php`:

```php
<?php

declare(strict_types=1);

use App\Livewire\Organizations\Create;
use App\Livewire\Organizations\Edit;
use App\Models\Organization;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('renders the create page', function (): void {
    actingAs(User::factory()->createOne());

    get(route('organizations.create'))->assertOk()->assertSeeLivewire(Create::class);
});

it('creates an organization and assigns it to the current user', function (): void {
    $user = User::factory()->createOne(['organization_id' => null]);
    actingAs($user);

    livewire(Create::class)
        ->set('name', 'Acme')
        ->set('tax_number', '12345678-1-42')
        ->call('save')
        ->assertRedirect(route('organizations.myorganization'));

    assertDatabaseHas(Organization::class, ['name' => 'Acme']);

    expect($user->fresh()->organization_id)->not->toBeNull();
});

it('requires a name and a tax number', function (): void {
    actingAs(User::factory()->createOne());

    livewire(Create::class)
        ->set('name', '')
        ->set('tax_number', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required', 'tax_number' => 'required']);
});

it('updates an organization', function (): void {
    actingAs(User::factory()->createOne());
    $organization = Organization::factory()->createOne(['name' => 'Old']);

    livewire(Edit::class, ['organization' => $organization])
        ->set('name', 'New')
        ->call('save')
        ->assertRedirect(route('organizations.index'));

    expect($organization->fresh()->name)->toBe('New');
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/OrganizationFormsTest.php`
Expected: FAIL — az osztályok nem léteznek.

- [ ] **Step 3: Generáld a komponenseket**

```bash
php artisan make:livewire Organizations/Create --no-interaction
php artisan make:livewire Organizations/Edit --no-interaction
```

- [ ] **Step 4: Írd meg a Create komponenst**

A `Tools\Create` (Task 8) szerkezetét kövesd, a fenti validációs szabályokkal. A `save()` tranzakcióján belül, a `Organization::query()->create(...)` után:

```php
            /** @var User $user */
            $user = Auth::user();
            $user->organization_id = $organization->id;
            $user->save();
```

Átirányítás: `$this->redirectRoute('organizations.myorganization', navigate: true);`
Flash üzenet: `__('Organization created successfully.')`

- [ ] **Step 5: Írd meg az Edit komponenst**

`public Organization $organization;`, `mount()`-ban feltöltés, `save()`-ben `$this->organization->update($validated)`, átirányítás az `organizations.index`-re, üzenet: `__('Organization updated successfully.')`.

- [ ] **Step 6: Írd meg a nézeteket és töröld a régieket**

A régi `resources/views/organization/create.blade.php` és `edit.blade.php` tartalmát vidd át, `<x-layouts.app>` nélkül, `wire:submit="save"` és `wire:model` használatával. Az `edit.blade.php`-ben tartsd meg a Task 9-ben javított `<livewire:organizations.users-table ... />` sort.

```bash
rm resources/views/organization/create.blade.php resources/views/organization/edit.blade.php
```

- [ ] **Step 7: Írd át a route-okat**

```php
    Route::livewire('organizations/create', Organizations\Create::class)->name('organizations.create');
    Route::livewire('organizations/{organization}/edit', Organizations\Edit::class)->name('organizations.edit');
```

Töröld az `organizations.store` és `organizations.update` route-okat. Az `OrganizationController`-ből töröld a `create()`, `store()`, `edit()`, `update()` metódusokat.

- [ ] **Step 8: Futtasd a teszteket**

Run: `php artisan test tests/Feature/OrganizationFormsTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 9: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert organization create and edit to Livewire components"
```

---

### Task 11: Organizations\MyOrganization — három akció-route beolvasztásával

Ez a szelet szünteti meg a két maradék GET-en elérhető adatmódosítást.

**Files:**
- Create: `app/Livewire/Organizations/MyOrganization.php`
- Create: `resources/views/livewire/organizations/my-organization.blade.php`
- Delete: `resources/views/organization/myorganization.blade.php`
- Modify: `routes/web.php`, `app/Http/Controllers/OrganizationController.php`
- Modify: `tests/Feature/MyOrganizationPageTest.php`

**Interfaces:**
- Consumes: `App\Livewire\Organizations\UsersTable` (Task 9).
- Produces: `App\Livewire\Organizations\MyOrganization` a következő akciókkal:
  - `public function updateOrganization(): void` — a mai `myOrganizationUpdate()`
  - `public function moveProduct(int $productId, int $fromUserId, int $toUserId): void` — a mai `productMove()`
  - `public function detachProduct(int $userId, int $productId): void` — a mai `removeUserProduct()`
  - `public function removeMember(int $userId): void` — a mai `removeUserFromOrganization()`

**Jogosultsági hiba, amit itt javítunk:** a mai `removeUserProduct()` és `removeUserFromOrganization()` így szűr (`app/Http/Controllers/OrganizationController.php:166` és `:229`):

```php
if ($user->organization_id !== $authUser->organization_id) { ... }
```

Ha **mindkettő `null`**, a feltétel hamis, és az ellenőrzés átengedi őket — egy szervezet nélküli felhasználó hozzáférhet egy másik szervezet nélküli felhasználóhoz. Az új metódusokban a feltétel legyen:

```php
if ($authUser->organization_id === null || $user->organization_id !== $authUser->organization_id) { ... }
```

- [ ] **Step 1: Írd át és bővítsd a meglévő tesztet**

A `tests/Feature/MyOrganizationPageTest.php` meglévő tesztje maradjon változatlan, csak add hozzá az `assertSeeLivewire(MyOrganization::class)` ellenőrzést. Új tesztek:

```php
it('no longer exposes GET routes for mutations', function (string $name): void {
    expect(Route::has($name))->toBeFalse();
})->with([
    'organizations.detach',
    'organizations.removeUserFromOrganization',
    'organizations.productMove',
    'organizations.myorganizationupdate',
]);

it('refuses to remove a member from a different organization', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $outsider = User::factory()->createOne(['organization_id' => null]);

    actingAs($organizer);

    livewire(MyOrganization::class)->call('removeMember', $outsider->id);

    expect(User::query()->whereKey($outsider->id)->exists())->toBeTrue();
});

it('refuses to remove a member when the actor has no organization', function (): void {
    $actor = User::factory()->createOne(['organization_id' => null]);
    $target = User::factory()->createOne(['organization_id' => null]);

    actingAs($actor);

    livewire(MyOrganization::class)->call('removeMember', $target->id);

    expect(User::query()->whereKey($target->id)->exists())->toBeTrue();
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/MyOrganizationPageTest.php`
Expected: FAIL.

- [ ] **Step 3: Generáld és írd meg a komponenst**

```bash
php artisan make:livewire Organizations/MyOrganization --no-interaction
```

A négy akciót az `app/Http/Controllers/OrganizationController.php` megfelelő metódusaiból vidd át, változatlan tranzakció-kezeléssel, a fenti jogosultsági javítással. A `mount()` töltse be a szervezetet:

```php
    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $organization = Organization::query()
            ->with('users.products')
            ->whereKey($user->organization_id)
            ->first();

        if (! $organization instanceof Organization) {
            $this->redirectRoute('organizations.create', navigate: true);

            return;
        }

        $this->organization = $organization;
    }
```

- [ ] **Step 4: Írd meg a nézetet**

A régi `resources/views/organization/myorganization.blade.php` tartalma, `<x-layouts.app>` nélkül. Minden `route('organizations.detach', ...)`, `route('organizations.removeUserFromOrganization', ...)` link helyére `wire:click` gomb kerül, `wire:confirm`-mal a törléshez. A `productMove` űrlap `wire:submit`-re vált.

- [ ] **Step 5: Írd át a route-okat és töröld a controllert**

```php
        Route::livewire('/myorganization', Organizations\MyOrganization::class)->name('myorganization');
```

Töröld ezeket a route-okat: `organizations.detach`, `organizations.productMove`, `organizations.myorganizationupdate`, `organizations.removeUserFromOrganization`.

Az `OrganizationController`-ből ezzel minden metódus elfogyott a `destroy()` kivételével — azt a Task 9 `Organizations\Index` record action-je már átvette, tehát:

```bash
rm app/Http/Controllers/OrganizationController.php
rm resources/views/organization/myorganization.blade.php
```

Töröld az `organizations.destroy` route-ot és a `use App\Http\Controllers\OrganizationController;` importot.

- [ ] **Step 6: Futtasd a teszteket**

Run: `php artisan test tests/Feature/MyOrganizationPageTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 7: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert my-organization to a full-page Livewire component

Absorbs four action routes, removing two data-mutating GET endpoints
(detach, removeUserFromOrganization) that had no CSRF protection.

Also fixes an authorization hole: the previous membership check compared
two organization_id values without a null guard, so a user with no
organization could act on any other user with no organization."
```

---

### Task 12: Organizations\CreateEmployee és Profile\Edit

Az utolsó két controller.

**Files:**
- Create: `app/Livewire/Organizations/CreateEmployee.php`, `app/Livewire/Profile/Edit.php`
- Create: `resources/views/livewire/organizations/create-employee.blade.php`, `resources/views/livewire/profile/edit.blade.php`
- Delete: `app/Http/Controllers/EmployeeController.php`, `app/Http/Controllers/ProfileController.php`
- Delete: `resources/views/organization/createEmployee.blade.php`, `resources/views/profile/edit.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/EmployeeAndProfileTest.php` (create)

**Interfaces:**
- Consumes: a Task 8/10 űrlap-mintája.
- Produces: `App\Livewire\Organizations\CreateEmployee`, `App\Livewire\Profile\Edit`.

**Megjegyzés a Profile\Edit-hez:** a mai `resources/views/profile/edit.blade.php` három partialt include-ol (`update-profile-information-form`, `update-password-form`, `delete-user-form`). Ezek maradjanak külön Blade partialként a `resources/views/livewire/profile/partials/` alatt — a `Profile\Edit` komponens include-olja őket. A jelszóváltás továbbra is a `Auth\PasswordController`-en keresztül megy (`password.update` route), ahhoz nem nyúlunk.

- [ ] **Step 1: Írd meg a bukó tesztet**

`tests/Feature/EmployeeAndProfileTest.php`:

```php
<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Livewire\Organizations\CreateEmployee;
use App\Livewire\Profile\Edit;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    foreach (UserRole::cases() as $role) {
        Role::query()->firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
    }
});

it('renders the employee create page', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);

    actingAs($organizer);

    get(route('organizations.employee.create'))
        ->assertOk()
        ->assertSeeLivewire(CreateEmployee::class);
});

it('creates an employee in the organizer own organization', function (): void {
    $organization = Organization::factory()->createOne();
    $organizer = User::factory()->createOne(['organization_id' => $organization->id]);
    $organizer->assignRole(UserRole::Organizer);

    actingAs($organizer);

    livewire(CreateEmployee::class)
        ->set('name', 'New Employee')
        ->set('email', 'employee@example.com')
        ->set('password', 'password123')
        ->call('save')
        ->assertRedirect(route('organizations.myorganization'));

    assertDatabaseHas(User::class, [
        'email' => 'employee@example.com',
        'organization_id' => $organization->id,
    ]);
});

it('renders the profile page', function (): void {
    actingAs(User::factory()->createOne());

    get(route('profile.edit'))->assertOk()->assertSeeLivewire(Edit::class);
});

it('updates the profile', function (): void {
    $user = User::factory()->createOne(['name' => 'Old']);
    actingAs($user);

    livewire(Edit::class)
        ->set('name', 'New')
        ->call('save');

    expect($user->fresh()->name)->toBe('New');
});
```

- [ ] **Step 2: Futtasd, hogy lásd a bukást**

Run: `php artisan test tests/Feature/EmployeeAndProfileTest.php`
Expected: FAIL — az osztályok nem léteznek.

- [ ] **Step 3: Generáld a komponenseket**

```bash
php artisan make:livewire Organizations/CreateEmployee --no-interaction
php artisan make:livewire Profile/Edit --no-interaction
```

- [ ] **Step 4: Írd meg a CreateEmployee komponenst**

A logika az `app/Http/Controllers/EmployeeController.php:22-46`-ból jön. A validációs szabályokat az `app/Http/Requests/StoreUserRequest.php`-ból vedd át `#[Validate]` attribútumként. A `save()` a szervezet-azonosítót a bejelentkezett felhasználótól veszi, és a létrehozott felhasználóra `assignRole('Servicer')`-t hív.

- [ ] **Step 5: Írd meg a Profile\Edit komponenst**

A logika az `app/Http/Controllers/ProfileController.php` `edit()`, `update()` és `destroy()` metódusaiból jön. Az `update()` mellékhatását tartsd meg: ha az email megváltozott, az `email_verified_at` nullázódik.

A `destroy()` a jelszó megerősítése után kijelentkeztet, invalidálja a sessiont és regenerálja a tokent — ezt a logikát változatlanul vidd át.

- [ ] **Step 6: Írd meg a nézeteket, töröld a régieket**

```bash
mkdir -p resources/views/livewire/profile/partials
git mv resources/views/profile/partials/update-profile-information-form.blade.php resources/views/livewire/profile/partials/
git mv resources/views/profile/partials/update-password-form.blade.php resources/views/livewire/profile/partials/
git mv resources/views/profile/partials/delete-user-form.blade.php resources/views/livewire/profile/partials/
rm resources/views/profile/edit.blade.php
rm resources/views/organization/createEmployee.blade.php
rmdir resources/views/profile resources/views/organization 2>/dev/null || true
```

A `livewire/profile/edit.blade.php` include-jait igazítsd az új útvonalakra.

- [ ] **Step 7: Írd át a route-okat és töröld a két controllert**

```php
        Route::livewire('/create', Organizations\CreateEmployee::class)->name('employee.create');
```

Töröld az `organizations.employee.store` route-ot.

```php
    Route::livewire('/profile', Profile\Edit::class)->name('profile.edit');
```

Töröld a `profile.update` és `profile.destroy` route-okat.

```bash
rm app/Http/Controllers/EmployeeController.php
rm app/Http/Controllers/ProfileController.php
```

Töröld a hozzájuk tartozó importokat a `routes/web.php`-ból.

- [ ] **Step 8: Futtasd a teszteket**

Run: `php artisan test tests/Feature/EmployeeAndProfileTest.php`
Expected: PASS.

Run: `php artisan test`
Expected: minden zöld.

- [ ] **Step 9: Formázz és commitolj**

```bash
vendor/bin/pint --dirty
git add -A
git commit -m "feat: convert employee creation and profile to Livewire components

EmployeeController and ProfileController are removed. Only the three
auth endpoint controllers remain in app/Http/Controllers."
```

---

### Task 13: Zárás — ellenőrzés és takarítás

**Files:**
- Modify: `routes/web.php` (végső rendezés)
- Test: `tests/Feature/MigrationCompleteTest.php` (create)

**Interfaces:**
- Consumes: minden korábbi task.
- Produces: semmi új kód, csak bizonyíték.

- [ ] **Step 1: Írd meg a záró tesztet**

`tests/Feature/MigrationCompleteTest.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('keeps only the auth endpoint controllers', function (): void {
    $controllers = collect(glob(app_path('Http/Controllers/*.php')))
        ->map(fn (string $path): string => basename($path))
        ->sort()
        ->values()
        ->all();

    expect($controllers)->toBe(['Controller.php']);
});

it('routes every web page through a Livewire component', function (string $name): void {
    expect(Route::has($name))->toBeTrue();
})->with([
    'index',
    'products.index',
    'products.search',
    'products.myproducts',
    'products.edit',
    'tools.index',
    'tools.create',
    'tools.edit',
    'organizations.index',
    'organizations.create',
    'organizations.edit',
    'organizations.myorganization',
    'organizations.employee.create',
    'profile.edit',
]);
```

- [ ] **Step 2: Futtasd**

Run: `php artisan test tests/Feature/MigrationCompleteTest.php`
Expected: PASS. Ha az első teszt bukik, nézd meg, melyik controller maradt bent, és hogy indokolt-e.

- [ ] **Step 3: Ellenőrizd a nem regisztrált route-hivatkozásokat**

Run:

```bash
php artisan route:list --json | php -r '$r=json_decode(stream_get_contents(STDIN),true)??[]; foreach($r as $x){if($x["name"]??null) echo $x["name"]."\n";}' | sort > /tmp/registered.txt
grep -rhoE "route\('[a-zA-Z0-9._-]+'|to_route\('[a-zA-Z0-9._-]+'" app resources routes | grep -oE "'[^']+'" | tr -d "'" | sort -u > /tmp/used.txt
comm -23 /tmp/used.txt /tmp/registered.txt
```

Expected: üres kimenet, vagy csak a `mail` (az a `Notification::route('mail', ...)` hívásból jön, nem route-név).

Ha bármi más megjelenik, az egy törött hivatkozás — javítsd.

- [ ] **Step 4: Ellenőrizd, hogy nincs feloldatlan Livewire hivatkozás**

Gyűjtsd ki a nézetekben hivatkozott komponensneveket, és oldd fel mindegyiket a Livewire v4 `Finder`-ével:

```bash
grep -rhoE "<livewire:[a-z0-9.:-]+|@livewire\('[a-z0-9.:-]+'" resources/views \
  | sed "s/<livewire://;s/@livewire('//" | sort -u | tr '\n' ' '
```

A kapott neveket add át ennek a parancsnak (a `NEVEK` helyére, szóközzel elválasztva):

```bash
php artisan tinker --execute='
$f = app(\Livewire\Finder\Finder::class);
foreach (explode(" ", "NEVEK") as $n) {
    if ($n === "") { continue; }
    $ok = $f->resolveClassComponentClassName($n)
        || $f->resolveSingleFileComponentPath($n)
        || $f->resolveMultiFileComponentPath($n);
    printf("%-40s %s\n", $n, $ok ? "OK" : "HIÁNYZIK");
}'
```

Expected: minden név `OK`. Egyetlen elfogadott kivétel a `notifications` — azt a Filament regisztrálja futásidőben, a `Finder` nem látja.

- [ ] **Step 5: Teljes tesztfutás**

Run: `php artisan test`
Expected: minden zöld, az assertion-szám érdemben magasabb a kiinduló 161-nél.

- [ ] **Step 6: Formázás és rector**

```bash
vendor/bin/pint
vendor/bin/rector --dry-run
```

A rector kimenetét nézd át; ha ésszerű javaslatokat tesz az új komponensekre, futtasd le élesben, majd futtasd újra a teszteket.

- [ ] **Step 7: Commit**

```bash
git add -A
git commit -m "test: assert the Livewire migration is complete

Every web page now routes through a Livewire full-page component; only the
auth endpoint controllers remain."
```

---

## Self-review jegyzet

**Spec-lefedettség:** a spec minden szakaszához tartozik task — Fázis 0 → Task 1; Products → Task 3–7; Tools → Task 8; Organizations → Task 9–11; Profile → Task 12; Index → Task 2; `ProductEdit` szétszedése → Task 6; a záró ellenőrzés → Task 13. A spec `ToolSearch`-kivétele szándékosan nem kap taskot: a komponens és a tesztje marad.

**Ismert korlát:** a Task 3, 4, 5, 8, 10, 11, 12 nézet-lépései a meglévő Blade fájlok tartalmának átemelését írják elő ahelyett, hogy a teljes markupot beidéznék. Ez migrációnál pontos utasítás — a forrásfájl és a sorhely meg van adva —, de azt jelenti, hogy a kivitelezőnek el kell olvasnia a forrásnézetet, nem tudja pusztán a tervből kimásolni.

**Sorrendfüggőség:** a taskok szigorúan egymásra épülnek. A Task 7 névütközést old fel az `App\Livewire\Index` (Task 2) és az `App\Livewire\Products\Index` között — ezt ne hagyd ki.
