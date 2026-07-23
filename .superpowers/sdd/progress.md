# Livewire v4 full-page migráció — haladás

Terv: docs/superpowers/plans/2026-07-23-livewire-v4-full-page.md
Branch: feature/livewire-v4-full-page
Kiindulás: 77 teszt, 161 assertion, 0 bukó

Task 1: complete (commits 09ac229..31fb555, review clean, 96 tests / 180 assertions)
Task 2: complete (commits 31fb555..ece6049, review clean, 97 tests / 182 assertions) — App\Livewire\Index átnevezve Home-ra Livewire névfeloldási ütközés miatt
Task 3: complete (commits bbc7825..3513e78, review clean, 100 tests / 188 assertions)
  Minor (pre-existing, for final review triage): addToMyProducts() can attach the same product twice — product_user pivot has no unique constraint; identical to the deleted ProductController::add(). Candidate fix: syncWithoutDetaching().
Task 4: complete (commits 3513e78..3a10c2d, review clean after fix, 102 tests / 191 assertions) — dupla delete-értesítés javítva
Task 5: complete (commits 3a10c2d..0708476, review clean, 103 tests / 193 assertions) — ProductController törölve; dangling route-hivatkozás javítva
Task 6: complete (commits 0708476..788a550, review clean, 112 tests / 214 assertions) — Edit.php 593→433; MaintenanceWindow a VALÓDI 11-13 hónapos szabályt kódolja (a terv hibás ±1/+2 képlete javítva), 3 duplikált blokk megszüntetve
  Minor (pre-existing, final review triage): kikommentezett halott sor az eventForm()-ban, verbatim mozgatás miatt maradt bent.
Task 7: complete (commits 788a550..60928e9, review clean, 114 tests / 219 assertions)
  *** IMPORTANT, DÖNTÉST IGÉNYEL: products.index minden terméket listáz (owner_name, cím, sorozatszám) bármely bejelentkezett felhasználónak — a route-on csak auth+verified van, nincs role-korlát. Migráció előtt a lap TÖRÖTT volt (nem létező komponensre mutatott), tehát a PII most válik ténylegesen elérhetővé. Eldöntendő: kapjon-e role:Admin|Super Admin middleware-t.
  Minor: a Task 7 report tévesen 'megtartott' searchable()-t írt a tool.name oszlopnál, valójában újat adott hozzá (jóindulatú, egyezik a MyProducts-szal).
Task 8: complete (commits 60928e9..2d5afe8, review clean, 121 tests / 237 assertions) — ToolController törölve
  *** VALÓDI, PRE-EXISTING BUG (nem a migráció okozza, nem is javítja): a tool create/edit űrlap category legördülője 'Boiler'/'Heat pump' értékeket kínál, de a Tool::category a ProductCategory enumra van castolva (sime/ferroli/sprsun/sunrain/kazán). Bármelyik kiválasztása ValueError-t dob, amit a try/catch lenyel — vagyis kategóriát MENTENI JELENLEG NEM LEHET.
  Minor: create-input-text.blade.php (megosztott komponens) hatókörön kívül lett módosítva; a reviewer minden hívóját ellenőrizte, egyik sem ad át extra attribútumot, tehát inert.
Task 9: complete (commits 2d5afe8..b4f0d70, review clean, 124 tests / 244 assertions)
  Important (pre-existing, nem regresszió): szervezet törlése hard delete, a users.organization_id-n nincs FK constraint (a migráció foreignIdFor-t használ ->constrained() nélkül), így a törlés árván hagyja a felhasználókat. Ugyanez volt az OrganizationController::destroy()-ban is, de most egy jól látható UI belépési pont is lett rá.
  Minor: OrganizationController::index() halott lett (a route átirányítva) — Task 10/11 takarítja.
Task 10: complete (commits b4f0d70..a20abd2, review clean, 128 tests / 258 assertions) — Edit validáció szándékosan lazább (csak name required), hűen reprodukálja a törölt OrganizationController::update()-et
