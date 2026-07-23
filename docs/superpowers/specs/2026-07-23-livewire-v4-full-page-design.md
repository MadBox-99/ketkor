# Livewire v4 full-page class-based migráció

**Dátum:** 2026-07-23
**Branch:** `feature/livewire-v4-full-page`
**Állapot:** jóváhagyva, implementációra vár

## Cél

A `routes/web.php` alatti controller-alapú Blade oldalak átállítása Livewire v4 full-page,
class-based komponensekre. A Filament admin panel változatlanul megmarad mellette.

A `routes/auth.php` már át van állítva — az ott használt minta (`Route::livewire()` +
`App\Livewire\Auth\*` + `#[Layout]`) a mérce, nem vezetünk be új konvenciót.

## Kiinduló állapot

### Már kész

- `routes/auth.php`: 6 `Route::livewire()` full-page komponens.
- `config/livewire.php`: `make_command.type => 'class'`, tehát a `make:livewire` class-based
  komponenst gyárt, nem v4-alapértelmezett single-file-t.

### Törött: nem létező Livewire komponensre mutató nézetek

Livewire v4 `Finder`-rel ellenőrizve — sem osztály, sem SFC, sem MFC nem tartozik hozzájuk:

| Nézet | Hivatkozott komponens | Érintett route |
|---|---|---|
| `resources/views/product/index.blade.php:7` | `product-filament-table` | `products.index` |
| `resources/views/organization/index.blade.php:9` | `organizations-table` | `organizations.index` |
| `resources/views/tool/index.blade.php:9` | `tools-table` | `tools.index` |
| `resources/views/organization/edit.blade.php:45` | `organization-details-users-table` | `organizations.edit` |

### Halott: regisztrált route, üres metódus

13 stub akció (`{ // }` törzs): a teljes `PartialController` CRUD-ja (6), a
`ProductLogController` CRUD-ja (4), valamint `OrganizationController::show`,
`ProductController::show`, `ToolController::show`.

### Halott: kód route nélkül

A kódban hivatkozott, de soha nem regisztrált route-nevek: `logs.index`, `logs.destroy`,
`users.index`, `users.show`, `users.store`, `users.update`.

- `LogController` — nincs `logs` resource a `web.php`-ban. A `resources/views/log/` is ezzel dől.
- `ProfileController::index/create/store/show/userUpdate` — nincs `users` resource.
  A `resources/views/user/` három nézete is ezzel dől.

### Halott: duplikáció

- `PartialController::store` és `ProductLogController::store` ugyanazt csinálja, amit az
  `App\Livewire\ProductEdit` már megvalósít (partial létrehozás, garanciahosszabbítás
  karbantartás-ablakkal). Egyik sincs hivatkozva egyetlen nézetből sem.
  A `ProductEdit`-beli változat az újabb és tesztelt (`ProductEditEventValidationTest`,
  `ProductEditIntegrationTest`).
- `products.remove` route ugyanazt teszi, mint a `ProductSearchUser` `delete` record action-je.
- `App\Livewire\ToolSearch` létezik és van rá teszt, de egyetlen nézet sem hivatkozik rá.

### Biztonsági megjegyzés

Három adatmódosító művelet GET-en érhető el, tehát CSRF-védelem nélkül, egy link
megnyitásával kiváltható:

- `products.add` — terméket rendel a felhasználóhoz
- `organizations.detach` — leválaszt egy terméket
- `organizations.removeUserFromOrganization` — **felhasználót töröl**

Mindhárom megszűnik azzal, hogy komponens-metódussá válik.

## Döntések

| Kérdés | Döntés |
|---|---|
| Hatókör | Minden route migrál; a Filament panel párhuzamosan megmarad |
| Hiányzó táblázatok | Filament Table (`HasTable` + `InteractsWithTable`), a `ProductSearchUser` mintájára |
| Halott stubok | Törlés a route-jukkal együtt |
| Névtér | Domén szerint csoportosítva: `App\Livewire\{Domén}\{Akció}` |
| Akció-route-ok | Beolvadnak a hívó komponens metódusába |
| Végrehajtás | Doménenkénti függőleges szeletek |
| `ProductEdit` szétszedése | Belekeverve a Products szeletbe |

## Architektúra

- **Route:** `Route::livewire('/út', Komponens::class)->name('...')`. A middleware-ek
  változatlanul a route-on maradnak, beleértve a `role:Organizer|Admin|Super Admin` csoportot.
- **Komponens:** `App\Livewire\{Domén}\{Akció}`, class-based, `#[Layout('components.layouts.app')]`.
- **Nézet:** `resources/views/livewire/{domén}/{akció}.blade.php`.
- **Validáció:** `#[Validate]` attribútum a property-ken, ahogy az `Auth\Login` csinálja.
- **Tranzakciók:** a `DB::beginTransaction()/commit()/rollback()` blokkok a komponens-metódusba
  költöznek, változatlan szemantikával.
- **`Route::resource` helyett** explicit route-lista, hogy pontosan látszódjon, mi létezik.

## Fázis 0 — takarítás

Migráció előtt, hogy a felület felére csökkenjen.

| Törlendő | Indok |
|---|---|
| `LogController`, `resources/views/log/` | Nincs route |
| `PartialController`, `Route::resource('partials')` | 6 stub + 1 duplikált `store` |
| `ProductLogController`, `Route::resource('productlogs')` | 4 stub + 1 duplikált `store` |
| `ProfileController::index/create/store/show/userUpdate`, `resources/views/user/` | Nincs `users` route |
| `OrganizationController::show`, `ProductController::show`, `ToolController::show` | Üres stub |

Minden törölt funkció megvan a Filament panelben (`LogResource`, `PartialResource`,
`ProductLogResource`, `UserResource`).

**Kivétel — `App\Livewire\ToolSearch`:** halott kód (egyetlen nézet sem hivatkozik rá),
de van rá élő teszt (`tests/Feature/ToolSearchTest.php`). A CLAUDE.md tiltja tesztek
engedély nélküli törlését, ezért a komponens **és a tesztje is a helyén marad**, amíg
külön jóváhagyás nem érkezik a törlésére. Nem blokkolja a migráció többi részét.

## Szeletek

### Products

| Route | Komponens | Forrás |
|---|---|---|
| `products.index` | `Products\Index` | ÚJ — Filament Table, a törött `product-filament-table` helyett |
| `products.search` | `Products\Search` | `App\Livewire\ProductSearch` átnevezés |
| `products.myproducts` | `Products\MyProducts` | `App\Livewire\ProductSearchUser` átnevezés |
| `products.edit` | `Products\Edit` | `App\Livewire\ProductEdit` + `ProductController::edit` adatbetöltése |
| `products.update` | — | beolvad `Products\Edit`-be |
| `products.add` | — | beolvad `Products\Search`-be, `wire:click` |
| `products.remove` | — | route törlendő; a `MyProducts` record action-je már ezt csinálja |

A `ProductEdit` 500+ soros. A költöztetés során szét kell szedni: a schema-definíciók
(`productForm`, az esemény-űrlapok) és a validációs logika (`validateMaintenanceTiming`
és társai) külön osztályba kerülnek, a komponens csak az állapotot és az akciókat tartja.
A meglévő tesztek állításai nem változnak — ez a bizonyíték, hogy a szétszedés nem tört el semmit.

### Tools

| Route | Komponens | Forrás |
|---|---|---|
| `tools.index` | `Tools\Index` | ÚJ — Filament Table, a törött `tools-table` helyett |
| `tools.create` | `Tools\Create` | `ToolController::create` + `store` |
| `tools.edit` | `Tools\Edit` | `ToolController::edit` + `update` |
| `tools.destroy` | — | beolvad `Tools\Index` record action-be |
| `tools.show` | — | törlés (stub) |

### Organizations

| Route | Komponens | Forrás |
|---|---|---|
| `organizations.index` | `Organizations\Index` | ÚJ — Filament Table, a törött `organizations-table` helyett |
| `organizations.create` | `Organizations\Create` | `OrganizationController::create` + `store` |
| `organizations.edit` | `Organizations\Edit` | `OrganizationController::edit` + `update` |
| `organizations.myorganization` | `Organizations\MyOrganization` | `myOrganization` + `myOrganizationUpdate` + `productMove` + `detach` + `removeUserFromOrganization` |
| `organizations.employee.create` | `Organizations\CreateEmployee` | `EmployeeController::create` + `store` |
| `organizations.show` | — | törlés (stub) |
| `organizations.destroy` | — | beolvad `Organizations\Index` record action-be |
| — | `Organizations\UsersTable` | ÚJ, beágyazott — a törött `organization-details-users-table` helyett |

### Profile

| Route | Komponens | Forrás |
|---|---|---|
| `profile.edit` | `Profile\Edit` | `ProfileController::edit` + `update` + `destroy` |

### Index

A `/` jelenleg üres nézet (`resources/views/index.blade.php`, csak fejléc).
`App\Livewire\Index` lesz belőle.

## Végeredmény

Öt controller tűnik el teljesen: `LogController`, `PartialController`, `ProductLogController`,
`ToolController`, `EmployeeController`. A `ProductController`, `OrganizationController` és
`ProfileController` szintén, miután minden akciójuk komponensbe költözött.

Marad: `Auth/EmailVerificationNotificationController`, `Auth/PasswordController`,
`Auth/VerifyEmailController` — ezek nem oldalak, hanem POST/GET végpontok, és a
`routes/auth.php` már így használja őket.

Létrejön ~14 full-page és 2 beágyazott komponens.

## Tesztstratégia

A meglévő 77 teszt a kapaszkodó. Minden szelet végén `php artisan test` zöld.

- A négy komponens-teszt (`ProductSearchTest`, `ProductSearchUserTest`,
  `ProductEditIntegrationTest`, `ProductEditEventValidationTest`) az osztálynév-váltáskor
  átnevezésre szorul, de **az állításaik változatlanok maradnak**.
- Minden új oldalra smoke teszt: `assertOk` + `assertSeeLivewire`.
- Minden törölt route-ra `assertNotFound`.
- A három korábban GET-en elérhető adatmódosításra teszt, hogy GET-tel már nem hívható.

**Ismert, nem ide tartozó hiba:** `php artisan test --parallel` esetén 11 teszt bukik.
Ez Laravel 12-n is így volt, tehát nem a migráció okozza; a szeletek verifikációja
soros futtatással történik.

## Kockázatok

- A `ProductEdit` szétszedése a legnagyobb egyedi kockázat. Külön szeletben, a meglévő
  tesztekre támaszkodva történik, nem összevonva a route-váltással.
- A `web.php` hosszabb lesz az explicit route-listától, cserébe pontosan olvasható.
- A nézetek `route()` hívásai a törölt route-nevekre hibát dobnának — a takarítási
  fázisban a nézeteket is végig kell nézni, nem csak a controllereket.
