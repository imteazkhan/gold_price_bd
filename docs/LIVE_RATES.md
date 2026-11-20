# Live Gold & Silver Rate — Implementation Details

Ei document e bola ache website e **live price auto update** kivabe kaj kore, kon file e ki ache, ar kichu vul hole ki korben.

---

## 1. Data kotha theke ase

Reference repo: `github.com/bdgautam24-rgb/Live-Gold-price-Bangladesh`

Oi repo te nijer kono API nei. Tader `index.html` e shudhu goldr.org er widget script lagano ache:

```html
<script src="https://www.goldr.org/price.js?gtm"></script>
```

goldr.org er kono official/public JSON API document paoa jayni (`/api-documentation/` page ta Play Store e redirect kore). Tobe **`https://www.goldr.org/price.js`** file er bhetore latest price JavaScript constant hisebe JSON e thake:

```js
const GoldrPriceTable_goldData = [
  {"n":"২২ ক্যারেট সোনার দাম","bv":"২০১,১৪৬৳","sv":"১৬৪,৯৩৯৳","bg":"১৭,২৪৫৳","sg":"১৪,১৪১৳",
   "bv_raw":201145.68,"sv_raw":164939.4576,"bg_raw":17245,"sg_raw":14140.9},
  ...
];
const GoldrPriceTable_silverData = [
  {"n":"২২ ক্যারেট রুপার দাম","bv":"৩,০৩৩৳","bg":"২৬০৳","bv_raw":3032.64,"bg_raw":260},
  ...
];
const GoldrPriceTable_usdRate = 124.4841377;
```

Amra ei file ta **server theke** (browser theke na) download kori, JSON ta alada kori, ar nijer database e save kori.

### Field er mane

| Field | Mane | Amra ki kori |
|---|---|---|
| `n` | Bangla te nam, jemon "২২ ক্যারেট সোনার দাম" | Karat code banai (`22K`) |
| `bg_raw` | Prati **gram** er dam | `price_per_gram` e save |
| `bv_raw` | Prati **vhori** er dam (`bg_raw × 11.664`) | Save kori na, gram theke nije hisab kori |
| `sg_raw` | Prati gram **bikri dam** (BAJUS niyome 18% bad) | `sell_price_per_gram` e save (shudhu gold) |
| `sv_raw` | Prati vhori bikri dam | Save kori na |
| `GoldrPriceTable_usdRate` | 1 USD = koto BDT | Setting on thakle `usd_bdt` update |

> ⚠️ **24K source e nei.** 24K er rate admin theke manually update korte hobe.

---

## 2. Kaj er flow

```
                    ┌────────────────────── Trigger ──────────────────────┐
                    │                                                     │
  Windows Task Scheduler           Visitor er browser             Admin "Fetch live now"
  (php artisan schedule:run)       (GET /rates/live)              (POST /admin/rates/fetch)
          │                               │                                │
          ▼                               ▼                                ▼
  rates:fetch command ──────►  RateSyncService::syncIfDue()     RateSyncService::sync(force)
                                          │                                │
                                          └──────────────┬─────────────────┘
                                                         ▼
                                          Cache lock "rate-sync" (60s)
                                                         ▼
                                     GoldrRateFetcher::fetch()  ── HTTP GET price.js
                                                         ▼
                                     parse() → ['gold'=>[22K=>…], 'silver'=>[…], 'usd'=>…]
                                                         ▼
                                     apply(): protiti purity er jonno rule check
                                                         ▼
                              price_histories (ajker row)  +  settings  +  rate_fetch_logs
                                                         ▼
                              Public page /rates/live poll kore → DOM e notun price
```

---

## 3. File gulo

| File | Kaj |
|---|---|
| [app/Services/GoldrRateFetcher.php](../app/Services/GoldrRateFetcher.php) | `price.js` download ar parse kore, Bangla nam theke karat code banay |
| [app/Services/RateSyncService.php](../app/Services/RateSyncService.php) | Kokhon fetch hobe, ki save hobe tar rule; lock, log, status |
| [app/Services/RateService.php](../app/Services/RateService.php) | `currentRates()`, `liveSnapshot()` (live JSON), `copyText()` |
| [app/Console/Commands/FetchLiveRates.php](../app/Console/Commands/FetchLiveRates.php) | `php artisan rates:fetch [--force]` |
| [routes/console.php](../routes/console.php) | Scheduler: proti 5 minute e `rates:fetch` |
| [app/Http/Controllers/HomeController.php](../app/Http/Controllers/HomeController.php) | `live()` → `GET /rates/live` |
| [app/Http/Controllers/Admin/RateController.php](../app/Http/Controllers/Admin/RateController.php) | Manual save, "Fetch now", "Use live", auto settings |
| [app/Models/RateFetchLog.php](../app/Models/RateFetchLog.php) | Fetch log model |
| [database/migrations/2026_11_20_000010_add_auto_rate_sync.php](../database/migrations/2026_11_20_000010_add_auto_rate_sync.php) | `source`, `sell_price_per_gram` column + `rate_fetch_logs` table |
| [resources/views/admin/rates/index.blade.php](../resources/views/admin/rates/index.blade.php) | Admin: auto update card, manual form, fetch log |
| [resources/views/home.blade.php](../resources/views/home.blade.php) | Public page: `data-live` attribute + polling JS |

---

## 4. Fetch ar parse — `GoldrRateFetcher`

### HTTP request

```php
Http::timeout(10)->connectTimeout(5)->retry(2, 500, throw: false)
    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; GoldPriceBD/1.0)'])
    ->get('https://www.goldr.org/price.js');
```

- Connect korte 5 sec, puro response er jonno 10 sec, fail hole 500ms por 2 bar porjonto try.
- HTTP 200 na hole `RuntimeException` → log e `failed`.

### Parse

1. Regex diye JS constant er array ta ber kori:
   ```php
   '/GoldrPriceTable_goldData\s*=\s*(\[.*?\])\s*;/su'
   ```
   tarpor `json_decode`.
2. `GoldrPriceTable_usdRate` er number ta regex diye pori, 2 decimal e round.
3. Gold ar silver duitai khali hole exception: *"No recognisable prices found in source (format may have changed)"*.

### Karat chena

`n` field er Bangla digit (`০-৯`) age English e convert hoy, tarpor:

| Nam e ache | Karat code |
|---|---|
| `সনাতন` / `traditional` | `Traditional` |
| `22 ক্যারেট`, `22K`, `22 karat` … | `22K` (je 2 digit thake) |

Jei row er karat bojha jay na ba `bg_raw <= 0`, sheta bad deoya hoy.

**Fetch er result:**
```php
[
  'gold'   => ['22K' => ['gram' => 17245, 'sell' => 14140.9], '21K' => …, '18K' => …, 'Traditional' => …],
  'silver' => ['22K' => ['gram' => 260, 'sell' => null], …],
  'usd'    => 124.48,
]
```

`purities` table er `metal` + `karat` er sathe match kore save hoy. Tai admin e notun purity banale tar `karat` (jemon `21K`) source er sathe mille auto update pabe.

---

## 5. Save er rule — `RateSyncService::apply()`

Protiti `Purity` er jonno, jodi source e tar dam thake:

| # | Obostha | Ki hoy |
|---|---|---|
| 1 | Ajker row **manual**, ar "Live price overrides my manual price" **off** | **Kichu hoy na**, manual dam thake. Log: *"kept manual price"* |
| 2 | Live dam shesh dam theke **`auto_update_max_jump`% (default 20)** er beshi alada | **Reject**, sondehojonok data. Log: *"rejected … (more than 20% …)"* |
| 3 | Ajker row already `auto` ar dam same | Kichu hoy na |
| 4 | Ar sob khetre | `updateOrCreate(purity_id + ajker date)` → `price_per_gram`, `sell_price_per_gram`, `source = auto`, `note = "Auto: goldr.org"` |

Ek dine ektai row thake (`purity_id + recorded_on` unique). Dine kayekbar dam bodlale oi row update hoy, ar din sesh er dam ta history te thake.

`auto_update_usd` on thakle `settings.usd_bdt` o update hoy.

### Status

| Status | Mane |
|---|---|
| `success` | Ontoto 1 ta dam bodleche |
| `unchanged` | Kono dam bodlay ni |
| `skipped` | Bodlay ni, kintu kichu manual rakha hoyeche ba reject hoyeche |
| `failed` | Network/format error |

Protiti run er por:
- `settings` e `auto_last_fetch_at`, `auto_last_status`, `auto_last_message` save hoy
- `rate_fetch_logs` e ekta row: status, trigger, message, changes (`[{label, old, new}]`), duration_ms, user_id

### Lock

`Cache::lock('rate-sync', 60)`: ek shathe onek visitor ba cron + visitor ashleo fetch **ekbar** hoy. Lock na pele `null` return kore, kono error hoy na. Lock er jonno `CACHE_STORE=database` lagbe, ja `.env` e already ache.

---

## 6. Trigger — kokhon fetch hoy

`isDue()` = auto update **on** ebong shesh fetch er por `auto_update_interval` minute (default 30, minimum 5) par hoyeche.

### a) Scheduler (cron) — recommended

```php
// routes/console.php
Schedule::command('rates:fetch')->everyFiveMinutes()->withoutOverlapping();
```

Command ta proti 5 minute e chole, kintu `syncIfDue()` interval na hole kichu kore na. Tai admin theke interval bodlale cron bodlate hoy na.

**Windows (WAMP) e setup:** Task Scheduler → Create Task
- Trigger: Daily, *Repeat task every 1 minute*, duration *Indefinitely*
- Action: Program `C:\wamp64\bin\php\php8.2.x\php.exe` (nijer PHP path din)
  Arguments: `artisan schedule:run`
  Start in: `D:\wamp\www\ik-home\gold_price_bd`

**Linux server e:**
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### b) Visitor (cron chara fallback)

`GET /rates/live` (throttle 30/min):
```php
$sync->syncIfDue('visitor');   // due hole fetch kore
return response()->json($this->rates->liveSnapshot());
```
Page load er sathe sathe ar proti `live_refresh_seconds` (default 120) por JS eta call kore. Cron na thakleo keu site khulle dam notun hoy. Prothom page HTML fast thake, karon fetch hoy AJAX call e.

### c) Admin

- **Fetch live now** → `POST /admin/rates/fetch`: `force: true`, auto update off thakleo chole, interval mane na.
- **↺ Use live** (manual row er pashe) → same route `release_purity_id` soho: ajker manual row delete kore, tarpor fetch.

### d) CLI

```bash
php artisan rates:fetch          # due hole fetch (scheduler jeta chalay)
php artisan rates:fetch --force  # ekhoni fetch, setting jai thakuk
```
Output e protiti change dekhay, jemon `21K Gold   17815 -> 16470`.

---

## 7. Manual update

Admin → **Update rates** → date select → dam boshan → **Save manually**.

- Shudhu **je row er dam bodlecen** sheta `source = manual` hoy. Prefill kora auto dam same thakle sheta auto-i thake (`RateController::store`).
- Admin → Price history theke add/edit korleo `manual` hoy.
- Manual dam oi diner jonno lock thake, jodi na "Live price overrides my manual price" on kora thake. Porer din auto fetch notun row banay.

---

## 8. Database

### `price_histories` (notun column)
| Column | Type | Note |
|---|---|---|
| `source` | string(20), default `manual` | `auto` / `manual` / `seed` (demo data) |
| `sell_price_per_gram` | decimal(12,2), nullable | Source er `sg_raw` (bikri dam) |

### `rate_fetch_logs`
| Column | Note |
|---|---|
| `status` | success / unchanged / skipped / failed |
| `trigger` | schedule / visitor / admin / cli |
| `message` | Summary (max 500) |
| `changes` | JSON: `[{"label":"21K Gold","old":17815,"new":16470}]` |
| `duration_ms` | Fetch e koto somoy laglo |
| `user_id` | Admin trigger korle |
| `created_at` | |

### `settings` (key / default)
| Key | Default | Kaj |
|---|---|---|
| `auto_update_enabled` | `1` | Auto update on/off |
| `auto_update_interval` | `30` | Koto minute por por fetch (min 5) |
| `auto_update_override_manual` | `0` | `1` = live dam ajker manual dam ke replace kore |
| `auto_update_usd` | `1` | USD/BDT o update |
| `auto_update_max_jump` | `20` | Er beshi % change reject |
| `live_refresh_seconds` | `120` | Khola page koto sec por por check kore (min 30) |
| `auto_last_fetch_at` / `auto_last_status` / `auto_last_message` | — | Shesh fetch er obostha (system likhe) |

Sob setting admin → Update rates → **Live auto update** card theke bodlano jay.

---

## 9. Public page e live update

### `GET /rates/live` response
```json
{
  "updated_at": "2026-11-20T12:32:10+06:00",
  "updated_label": "today 12:32 PM",
  "usd_bdt": 124.48,
  "copy_text": "…Copy rates button er text…",
  "rates": [
    {"id": 2, "metal": "gold", "karat": "22K", "price": 17245, "vhori": 201145.68, "change": -1410, "change_percent": -7.56}
  ]
}
```
`updated_at` = `price_histories` er sobcheye notun `updated_at`, mane shesh je somoy dam asolei bodleche.

### HTML hook
Je element e dam dekhay tate `data-live="<purity id>"` ar `data-f="<field>"` ache:

| `data-f` | Dekhay |
|---|---|
| `price` | `৳17,245` |
| `price2` | `৳17,245.00` |
| `vhori` / `vhori2` | Vhori dam (0 / 2 decimal) |
| `change` | `▲ +৳180` / `▼ −৳90` (rate table) |
| `pill` | Hero card er change pill, color class soho |

Calculator button e `data-live-rate="<id>"` ache, jate `data-rate` update hoy.

### JS (`pollLive()`)
1. Page load e ekbar, tarpor `setInterval(liveEvery)`. Tab hidden thakle skip kore, abar visible hole sathe sathe check kore.
2. `updated_at` bodlale `applyLive()`: text update hoy, bodla element 1.6 sec amber highlight hoy, calculator abar hisab hoy, history cache clear hoye chart reload hoy, ar toast *"Prices updated"* dekhay.
3. Network fail hole kichu dekhay na, porer bar abar try kore.

---

## 10. Somossa hole

| Lokkhon | Karon / Somadhan |
|---|---|
| Log e `failed: Source returned HTTP 403/5xx` | goldr.org block korche ba down. Kichukkhon por try korun, emonite manual update korun |
| Log e `failed: No recognisable prices found…` | `price.js` er format bodleche. `GoldrRateFetcher::parse()` er regex/field update korte hobe (`curl https://www.goldr.org/price.js` diye dekhun) |
| `rejected … (more than 20% …)` | Dam onek beshi bodleche. Thik hole `auto_update_max_jump` baran ba manual save korun |
| `kept manual price` | Ajker manual dam ache. **↺ Use live** chapun ba override setting on korun |
| Dam update hoy na, log o nei | Auto update off? Cron chole? Keu site khuleche? Admin e "Last fetch" dekhun |
| 24K update hoy na | Source e 24K nei, manual update korun |
| Time 6 ghonta vul | `.env` e `APP_TIMEZONE=Asia/Dhaka` ache kina dekhun, tarpor `php artisan config:clear` |

Server error details: `storage/logs/laravel.log` (`Rate sync failed: …`).

---

## 11. Seema ar jhuki

- **Official API na:** goldr.org er embed script theke data pora hocche. Tader terms e eta allowed kina confirm kora hoyni. Public e chalanor age tader sathe kotha bolun ar site e source credit (goldr.org) din.
- **Format bodlale bhangbe:** regex nirbhor. Fail hole site shesh save kora dam dekhay, kono crash hoy na. Admin log e dekha jay.
- **24K nei**, manual rakhte hobe.
- Source er dam BAJUS announcement er sathe na mille admin manual save kore override korte paren.

---

## 12. Onno source jog korte chaile

1. `app/Services/` e notun fetcher class banan, `fetch()` jeno same shape return kore:
   `['gold' => ['22K' => ['gram' => float, 'sell' => ?float]], 'silver' => […], 'usd' => ?float]`
2. `RateSyncService` er constructor e `GoldrRateFetcher` er bodle notun class din (ba ekta interface banan).
3. `apply()` er `'note' => 'Auto: goldr.org'` lekha ta bodlan.
