# Karbantartás emlékeztető e-mail rendszer — terv

Dátum: 2026-07-23

## 1. Cél

A Digitális Munkalap rendszer nyilvántartja a gázkészülékek szervizelt állapotát és az elvégzett
karbantartások dátumát. Ez a funkció automatikus e-mail értesítést küld az ügyfeleknek a következő
karbantartás esedékessége előtt, illetve ismétlő figyelmeztetést, ha az esedékes karbantartás elmaradt.

## 2. Döntések

| Kérdés | Döntés |
| --- | --- |
| Címzett | A készülékhez rendelt `User`(ek) e-mail címe (`products.user_id` és a `user_product` kapcsolat) |
| Ciklus | Készülékenként 6 vagy 12 hónap, alapértelmezés 12 |
| Esedékesség alapja | Legutolsó `maintenance` munkalap `when` mezője; ha nincs, `products.installation_date` |
| Előidő | 30 nappal és 7 nappal az esedékesség előtt (konfigurálható) |
| Lejárt emlékeztető | 14 naponta, legfeljebb 3 alkalommal (konfigurálható) |
| Garancia feltétel | Csak akkor megy levél, ha `warrantee_date` kitöltött **és** még nem járt le |
| Státusz követés | `sent` / `failed` / `pending` — megnyitás-követés (tracking pixel) nincs |
| Sablon hatóköre | Egy globális sablon és globális kapcsolati adatok |
| Architektúra | Menetközben számított esedékesség + kiküldési napló mint idempotencia-kulcs |
| Ütemezés | Napi egyszeri Artisan command cron-ból |

Az architektúra melletti érv: a forrásadat (munkalapok) marad az egyetlen igazság, a napló pedig csak
tényt rögzít, nem szándékot. Így nincs származtatott dátum, amit szinkronban kellene tartani, amikor
egy munkalapot visszamenőleg rögzítenek vagy javítanak.

## 3. Adatmodell

### Migrációk

**`products` bővítés**

- `maintenance_interval_months` — `unsignedTinyInteger`, default `12` (érvényes érték: 6 vagy 12)
- `maintenance_reminders_enabled` — `boolean`, default `true`

**`users` bővítés**

- `maintenance_reminders_enabled` — `boolean`, default `true` (ügyfélszintű kikapcsolás)

**`maintenance_reminders` (új, kiküldési napló)**

| Oszlop | Típus | Megjegyzés |
| --- | --- | --- |
| `product_id` | FK `products` | |
| `user_id` | FK `users` | a címzett |
| `email` | string | a küldéskori cím, utólagos visszakereséshez |
| `stage` | string | `advance` / `overdue` / `manual` |
| `stage_key` | unsignedSmallInteger | `advance` esetén az előidő napokban (pl. 30 vagy 7); `overdue` esetén az ismétlés sorszáma (1–3); `manual` esetén egy készülék × ügyfél × esedékesség hármashoz tartozó, egyesével növekvő sorszám, hogy egy admin ismételt „Emlékeztető küldése most" akciója se ütközzön az egyedi indexen |
| `due_date` | date | a kiszámított esedékesség |
| `last_maintenance_at` | date, nullable | amiből számoltunk |
| `sent_at` | timestamp, nullable | |
| `status` | string | `sent` / `failed` / `pending` |
| `error` | text, nullable | hibaüzenet `failed` esetén |

**Unique index: `(product_id, user_id, due_date, stage, stage_key)`** — ez garantálja, hogy egy
szakasz egy esedékességre és egy címzettre egyszer megy ki, akkor is, ha a cron kétszer fut vagy
visszamenőleg pótol. A `stage_key` azért külön oszlop, mert az előidők a beállításokból jönnek: ha az
`advance_days` értéke `[30, 7]`-ről `[45, 14]`-re változik, a napló és a unique kulcs változtatás
nélkül működik tovább.

**`maintenance_reminder_settings` (új, egysoros globális beállítás)**

- `enabled` — `boolean`, default `true` (globális főkapcsoló)
- `advance_days` — `json`, default `[30, 7]`
- `overdue_repeat_days` — `unsignedSmallInteger`, default `14`
- `overdue_max_count` — `unsignedTinyInteger`, default `3`
- `contact_phone`, `contact_email`, `booking_url` — nullable string
- `email_subject` — string
- `email_body` — text

### Modellek és típusok

- `App\Models\MaintenanceReminder` — `belongsTo(Product)`, `belongsTo(User)`
- `App\Models\MaintenanceReminderSetting` — `::current()` singleton accessor, ami létrehozza a
  rekordot az alapértelmezésekkel, ha még nincs
- `App\Enums\MaintenanceReminderStage` — `Advance`, `Overdue`, `Manual` (utóbbi az admin felületről
  kézzel indított küldésekhez; lásd az 5. szakaszt)
- `App\Enums\MaintenanceReminderStatus` — `Sent`, `Failed`, `Pending` (utóbbi az az átmeneti állapot,
  amíg egy emlékeztető le van foglalva az adatbázisban, de a levél kiküldése még nem történt meg;
  lásd a 4. szakasz 5. lépését)
- `Product::lastMaintenanceLog()` — `hasOne(ProductLog)` `where('what', 'maintenance')`,
  `latestOfMany('when')`
- `Product::nextMaintenanceDueDate(): ?CarbonImmutable` — a közös számítás, amit a cron, az admin
  lista és a levél is használ

A meglévő `ProductLogType`-ban a `Maintenance` konstans már létezik; a lekérdezés arra épül.

### E-mail sablon

A `email_subject` és `email_body` a settings rekordban él, egyszerű szöveges változó-helyettesítéssel.
Támogatott változók:

`{{ owner_name }}`, `{{ serial_number }}`, `{{ tool_name }}`, `{{ maintenance_type }}`,
`{{ last_maintenance_date }}`, `{{ due_date }}`, `{{ contact_phone }}`, `{{ contact_email }}`,
`{{ booking_url }}`

A feloldást egy `App\Support\MaintenanceReminderTemplateRenderer` osztály végzi. Ismeretlen változó
nem dob kivételt, üres sztringre cserélődik. A levél Blade layoutja a meglévő
`resources/views/emails/worksheet.blade.php` mintáját követi, csak a törzs jön a beállításból.

## 4. Küldési logika

`App\Console\Commands\SendMaintenanceRemindersCommand` — `maintenance:send-reminders`, opciói:

- `--dry-run` — kiírja, mi menne ki, de nem küld és nem naplóz
- `--catch-up=N` — az elmúlt N napot is ellenőrzi visszamenőleg (default `1`)

Ütemezés a `routes/console.php`-ban: naponta egyszer 08:00-kor, `withoutOverlapping()`-gal.

A döntéslogika külön osztályban él (`App\Services\MaintenanceReminderScheduler`), hogy önállóan
tesztelhető legyen, és a manuális küldés akció is ugyanazt az utat használja.

A feldolgozás **készülék × címzett** párokra fut: egy készülékhez a `products.user_id` és a
`user_product` kapcsolat felhasználói tartoznak, duplikátum nélkül. Minden címzett külön
naplórekordot és külön levelet kap.

Páronként, sorrendben:

1. **Jogosultsági szűrő** — mindegyik feltétel kötelező:
   - a globális `enabled` be van kapcsolva
   - `product.maintenance_reminders_enabled`
   - a címzett `user.maintenance_reminders_enabled` és van érvényes e-mail címe
   - `product.warrantee_date` kitöltött **és** `>= ma`
2. **Alapdátum** — legutolsó `maintenance` munkalap `when`-je, ha nincs, `installation_date`.
   Ha egyik sincs, a készülék kimarad.
3. **Esedékesség** — alapdátum + `maintenance_interval_months` hónap.
4. **Szakasz eldöntése** a vizsgált napra:
   - ha van olyan `d` az `advance_days` tömbben, hogy `nap == due − d` → `advance`, `stage_key = d`
   - `nap > due` → `overdue`, `stage_key = floor((nap − due) / overdue_repeat_days)`,
     ha az érték 1 és `overdue_max_count` közé esik
   - egyébként nincs teendő
5. **Küldés** — `App\Mail\MaintenanceReminderMail` (`ShouldQueue`). A levél kiküldése előtt a
   naplórekord `pending` státusszal jön létre (vagy egy korábbi `pending`/`failed` sor kerül
   újrafelhasználásra) egy tranzakción belüli zárolással; ez foglalja le az emlékeztetőt, mielőtt a
   levél kimegy, így egy párhuzamos futás nem tudja ugyanazt duplán elküldeni — az egyedi index
   kényszeríti ki, hogy a foglalás egyszer sikerüljön. A levél sikeres kiküldése után a rekord `sent`
   státuszra vált, kivétel esetén `failed` státuszra és a hibaüzenettel. Egy készülék (vagy egy
   emlékeztető) hibája nem akasztja meg a futást: a hibát a hívó (`MaintenanceReminderScheduler::run()`)
   elkapja, naplózza, és a következő emlékeztetővel folytatja.

Két következmény, amit a terv tudatosan felvállal:

- A **pontos napi egyezés** miatt egy kimaradt cron-futás elveszítene egy emlékeztetőt. Ezt oldja meg
  a `--catch-up`; a unique index miatt a visszamenőleges ellenőrzés biztonságosan idempotens.
- Ha az ügyfél elvégezteti a karbantartást, az új munkalap eltolja az esedékességet, így az `overdue`
  sorozat magától elhal — nincs szükség külön leállító logikára.

## 5. Admin felület (Filament)

- **`MaintenanceReminderResource`** — csak olvasható lista: készülék, ügyfél, típus, előző
  karbantartás, esedékesség, szakasz, kiküldés ideje, státusz. Szűrők státuszra, szakaszra és
  dátumtartományra.
- **`MaintenanceReminderSettingsPage`** — globális főkapcsoló, előidők, ismétlési szabály, kapcsolati
  adatok és a szerkeszthető sablon, élő előnézettel egy minta készüléken.
- **`ProductResource`** — az űrlapon intervallum select (6 / 12 hónap) és emlékeztető kapcsoló; a
  táblában „Következő esedékesség" oszlop és **„Emlékeztető küldése most"** akció. Az akció a
  scheduler ugyanazon útját hívja, csak a dátum-egyezés feltételt hagyja ki — a jogosultsági szűrőt
  (garancia, kapcsolók, e-mail cím) nem —, és ugyanúgy naplóz, `manual` szakasszal, hogy az egyedi
  index ne ütközzön az automatikus `advance`/`overdue` bejegyzésekkel.
- **`UserResource`** — ügyfélszintű emlékeztető kapcsoló.

## 6. Tesztelés

Pest feature tesztek `Mail::fake()` és `travelTo()` párossal:

- a -30 és -7 napos találat levelet küld, a köztes napokon nem megy semmi
- lejárt garancia kizár; hiányzó (`null`) garanciadátum szintén kizár
- kikapcsolt globális főkapcsoló, kikapcsolt készülék és kikapcsolt ügyfél egyaránt kizár
- e-mail cím nélküli címzett kimarad
- fallback az `installation_date`-re, ha nincs karbantartási munkalap
- 6 hónapos intervallum a féléves ütemet adja
- `overdue` ismétlés 14 naponta, megállás `overdue_max_count` után
- kétszeri futás ugyanazon a napon nem duplázza a levelet (unique index)
- `--catch-up` pótolja a kihagyott napot, ismétlés nélkül
- új karbantartási munkalap megszakítja az `overdue` sorozatot
- SMTP-hiba `failed` státuszt naplóz, és a futás folytatódik a következő készülékkel
- `--dry-run` nem küld és nem naplóz

Emellett: `MaintenanceReminderTemplateRenderer` unit teszt (változó-helyettesítés, ismeretlen változó
üresen marad), Filament resource és settings page tesztek, valamint a manuális küldés akció tesztje.
