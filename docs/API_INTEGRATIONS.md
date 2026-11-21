# API & Widget Integration — Full Process

Ei document e project er **sob API ar widget** ek jaygay bola ache: kon ta kotha theke data ane, kivabe kaj kore, kon file e ache, ar kichu bhangle ki korben.

> goldr.org `price.js` theke **live price auto update** er puro details alada document e ache:
> [LIVE_RATES.md](LIVE_RATES.md). Ekhane shudhu short summary ache.

---

## 1. Ek nojore sob API

| # | API / Widget | Dhoron | Ke call kore | Data kothay jay | Kaj |
|---|---|---|---|---|---|
| 1 | `https://www.goldr.org/price.js` | External | **Server** (PHP) | Database e save | Ajker BD gold/silver dam + USD/BDT |
| 2 | `https://www.goldr.org/widget.js` → `chart2.json` | External | **Browser** | Save hoy na, shudhu dekhay | "Live market chart" (BAJUS dam er history, 2007 theke) |
| 3 | `https://s.tradingview.com/goldprice/widgetembed/` | External (iframe) | **Browser** | Save hoy na | International gold spot chart (USD / oz) |
| 4 | `GET /rates/live` | Nijer | Browser (polling) | — | Latest dam JSON + dorkar hole auto fetch chalu kore |
| 5 | `GET /history-data` | Nijer | Browser | — | Ek purity er dam er history (table/chart) |
| 6 | `POST /estimates` | Nijer | Browser (calculator) | `saved_estimates` table | Calculator er hisab save |

Mone rakhben: **shudhu #1 server theke chole ar database e data rakhe.** #2 ar #3 shudhu visitor er browser e dekhay. Amader server er sathe tader kono shomporko nei.

---

## 2. Puro chobi

```
                              ┌──────────── goldr.org ────────────┐
                              │  price.js        widget.js        │
                              │     │            chart2.json      │
                              └─────┼───────────────┬─────────────┘
                                    │ (server, PHP)  │ (browser, JS)
                                    ▼                │
  ┌──────────────── Amader Laravel server ─────────┐ │
  │ GoldrRateFetcher → RateSyncService             │ │
  │        │                                       │ │
  │        ▼                                       │ │
  │ MySQL: price_histories, settings,              │ │
  │        rate_fetch_logs, saved_estimates        │ │
  │        │                                       │ │
  │        ▼                                       │ │
  │ RateService ──► GET /rates/live                │ │
  │             ──► GET /history-data              │ │
  │             ◄── POST /estimates                │ │
  └────────┬───────────────────────────────────────┘ │
           │ JSON                                    │
           ▼                                         ▼
  ┌──────────────────── Visitor er browser (home page) ───────────────────┐
  │ Hero cards, rate table, calculator  ◄── /rates/live (polling)         │
  │ History table                       ◄── /history-data                 │
  │ Live market chart                   ◄── goldr widget.js + chart2.json │
  │ International spot chart (iframe)   ◄── TradingView                   │
  └───────────────────────────────────────────────────────────────────────┘
```

---

## 3. goldr.org `price.js` — live dam (short summary)

- Server `https://www.goldr.org/price.js` download kore. File er bhetore `GoldrPriceTable_goldData`, `GoldrPriceTable_silverData`, `GoldrPriceTable_usdRate` JS constant e JSON thake.
- Regex diye JSON ta ber kore. Gram er dam (`bg_raw`) ar sell-back dam (`sg_raw`) `price_histories` e ajker row te save hoy.
- `usdRate` `settings.usd_bdt` e save hoy, jodi admin e "auto update USD" on thake.
- Fetch hoy 4 bhabe: visitor (`/rates/live`), admin "Fetch now", `php artisan rates:fetch`, ar scheduler. Shesh fetch er por `auto_update_interval` (default 30 min) par na hole abar fetch hoy na.

Files: [GoldrRateFetcher.php](../app/Services/GoldrRateFetcher.php), [RateSyncService.php](../app/Services/RateSyncService.php).
Bistarito: [LIVE_RATES.md](LIVE_RATES.md).

---

## 4. goldr.org widget — "Live market chart"

**Kothay:** Home page → Price history section → "Live market chart"
([home.blade.php](../resources/views/home.blade.php), `goldr-widget-container` khujun)

### Code

```blade
{{-- <head> e, @push('head') er bhetore — widget.js er AGE load hote hobe --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js"></script>

{{-- Section er bhetore --}}
<div id="goldr-widget-container" class="w-full min-h-[300px] overflow-hidden"></div>
<script src="https://www.goldr.org/widget.js?fixed"></script>
```

### Kivabe kaj kore

1. Browser age **ApexCharts** library load kore. Widget nije chart library ane na, page e `ApexCharts` age theke thakte hobe.
2. `widget.js` load hoy. Eta page e `id="goldr-widget-container"` wala `<div>` khoje. **Ei id bodlano jabe na.**
3. Widget browser theke `https://www.goldr.org/chart2.json?vxg` fetch kore. Response e BAJUS dam er puro history thake:
   ```json
   [{"date":"2007-03-07","traditional":1372,"k18":1326,"k21":1547,"k22":1620}, ...]
   ```
   Field: `date`, ar 22K / 21K / 18K / Traditional er **gram proti dam (BDT)**.
4. Data date onujayi sort kore container er bhetore chart banay. Default timeframe 1 year, user nije timeframe bodlate pare.

### Mone rakhben

- Ei data amader database e **save hoy na**, amader server o dekhe na. Chart ta puro goldr.org er.
- Amader nijer "Price history" table er data ashe amader database theke (`/history-data`), goldr er chart theke na. Tai duita te ekdom same date er data na-o thakte pare.
- Source credit ("Source: goldr.org") chart er upore deya ache. Eta rakhben.

---

## 5. TradingView chart — International gold spot

**Kothay:** Home page → Price history section → "International gold spot (USD / oz)"
([home.blade.php](../resources/views/home.blade.php), `tradingview_gold` khujun)

Link ta goldprice.org er site e use kora TradingView embed. Amra eta `<iframe>` hisebe boshiyechi.

### URL er gothon

```
https://s.tradingview.com/goldprice/widgetembed/?hideideas=1&overrides=%7B%7D&enabled_features=%5B%5D&disabled_features=%5B%5D&locale=en#<CONFIG>
                                                 └──────────────── query (fixed) ────────────────────────────────┘  └ hash ┘
```

`#` er porer `<CONFIG>` ta holo URL-encode kora ekta **JSON object**. Chart er sob setting ekhane thake. Original link e eta lomba encode kora text chilo:

```
%7B%22symbol%22%3A%22TVC%3AGOLD%22%2C ...
```

Decode korle:

```json
{"symbol":"TVC:GOLD","interval":"D","theme":"White","timezone":"America/New_York", ...}
```

### Amra kivabe banai

Hath diye encode kora text er bodle PHP array rakhechi, jate setting bodlano shohoj hoy:

```php
$tvConfig = [
  'symbol'   => 'TVC:GOLD',
  'interval' => 'D',
  'theme'    => 'White',
  'timezone' => 'Asia/Dhaka',
  'watchlist'=> implode("\u{1f}", ['TVC:GOLD', 'TVC:SILVER', ...]),
  ...
];
$tvSrc = 'https://s.tradingview.com/goldprice/widgetembed/?...&locale=en#'
       . rawurlencode(json_encode($tvConfig, JSON_UNESCAPED_SLASHES));
```

- `json_encode` array ke JSON banay, `rawurlencode` seta URL e deyar moto encode kore. Result original link er sathe mile (test kora hoyeche).
- `watchlist` er symbol gulo **`\u001f`** (ASCII "unit separator") character diye alada. Comma diye na. TradingView eta-i chay.

### Setting gulo

| Key | Ekhon | Mane / ki dite paren |
|---|---|---|
| `symbol` | `TVC:GOLD` | Shuru te kon chart. `TVC:SILVER`, `OANDA:XAUUSD` ityadi |
| `interval` | `D` | Candle somoy: `1`, `5`, `15`, `60` (minute), `D`, `W`, `M` |
| `style` | `1` | `1` = candle, `2` = line, `3` = area |
| `theme` | `White` | `White` / `Dark` |
| `timezone` | `Asia/Dhaka` | Original e `America/New_York` chilo, BD time er jonno bodlano |
| `allow_symbol_change` | `1` | User symbol bodlate parbe kina |
| `hide_side_toolbar` | `0` | `1` dile bam pasher drawing toolbar lukay |
| `details` | `1` | Dan pashe symbol details panel |
| `watchlist` | Gold, Silver, Platinum, Palladium, Gold/Silver, Oil, EUR/USD, USD/JPY, HUI, XAU, BTC | Dan pasher list |
| `withdateranges` | `1` | Niche 1D / 5D / 1M ... button |
| `save_image` | `1` | Screenshot button |
| `utm_*`, `page-uri` | goldprice.org | Original link theke rakha. TradingView tracking er jonno |

### iframe

```html
<iframe id="tradingview_gold" src="{{ $tvSrc }}" title="Gold spot price chart"
        class="w-full h-full" frameborder="0" allowtransparency="true"
        scrolling="no" allowfullscreen loading="lazy"></iframe>
```

- Wrapper `div` er height: mobile e 420px, desktop e 520px (`h-[420px] sm:h-[520px]`).
- `loading="lazy"`: user scroll kore oi porjonto na gele iframe load hoy na, tai page fast thake.

### Mone rakhben

- Ei chart **international market er dam, USD proti troy ounce (31.1 g)** e dekhay. BD er BAJUS dam (BDT/gram) na. Tai title e "USD / oz" lekha.
- Data, update, sob TradingView er. Amader server kichui kore na.
- `goldprice` path ta goldprice.org er jonno TradingView er partner embed. Eta official public widget na, tai TradingView bondho kore dite pare. Bhangle TradingView er official [Advanced Chart widget](https://www.tradingview.com/widget-docs/widgets/charts/advanced-chart/) (`embed-widget-advanced-chart.js`) diye replace korun.

---

## 6. Nijer API — `GET /rates/live`

**Kaj:** Page khola thakle latest dam dey. Auto fetch er somoy hole goldr.org theke fetch o chalu kore.

| | |
|---|---|
| Route | `GET /rates/live` (name `rates.live`) |
| Controller | [HomeController::live()](../app/Http/Controllers/HomeController.php) |
| Data | [RateService::liveSnapshot()](../app/Services/RateService.php) |
| Limit | `throttle:30,1`: ek IP theke minute e 30 bar |
| Cache | `Cache-Control: no-store` |

### Process

1. `RateSyncService::syncIfDue('visitor')`: auto update on ar interval par hole goldr.org theke fetch kore save kore. Error hole chupchap thake, endpoint kokhono bhange na.
2. `liveSnapshot()` database theke public purity gulor (24K lukano) latest dam niye JSON banay.

### Response

```json
{
  "updated_at": "2026-11-21T17:16:20+06:00",
  "updated_label": "today 5:16 PM",
  "usd_bdt": 124.72,
  "copy_text": "Gold Price BD rates (BAJUS) — Nov 21, 2026\nGold:\n22K: ...",
  "rates": [
    {
      "id": 2, "metal": "gold", "karat": "22K",
      "price": 17245, "vhori": 201145.68,
      "sell": 14140.9, "previous": 17245,
      "change": 0, "change_percent": 0
    }
  ]
}
```

| Field | Mane |
|---|---|
| `updated_at` / `updated_label` | Shesh **safol** check er somoy ba shesh dam bodlanor somoy, je ta notun. Dam same thakleo check er por somoy agay |
| `usd_bdt` | `settings.usd_bdt` |
| `copy_text` | Header er "Copy rates" button er text |
| `price` | Gram proti dam (latest date er row) |
| `vhori` | `price × vhori_grams` (default 11.664) |
| `sell` | Sell-back dam / gram (BAJUS: 18% kom). Silver e `null`, karon goldr.org dey na |
| `previous` | Ager recorded diner dam / gram |
| `change`, `change_percent` | `price − previous`, ar shetar % |

### Browser e ki hoy (`pollLive()`, home.blade.php)

1. Page load e ekbar call kore, tarpor proti `live_refresh_seconds` (minimum 30) sec por por. Tab hidden thakle skip kore.
2. `updated_at` bodlale `applyLive()` chale: `data-live="<id>"` + `data-f="<field>"` wala sob element er text update kore.

   | `data-f` | Dekhay |
   |---|---|
   | `price` / `price2` | Gram dam (0 / 2 decimal) |
   | `vhori` / `vhori2` | Vhori dam (0 / 2 decimal) |
   | `sell` | Sell-back / gram (hero card) |
   | `previous` | Ager diner dam / gram (hero card) |
   | `change` | `▲ +৳180` (rate table) |
   | `pill` | Hero card er change pill (color soho) |

3. Kono dam asolei bodlale amber highlight, calculator recalculate, history reload ar "Prices updated" toast hoy.

---

## 7. Nijer API — `GET /history-data`

**Kaj:** Ek purity er dam er history. Home page er "Price history" table eta use kore.

| | |
|---|---|
| Route | `GET /history-data` (name `history.data`) |
| Controller | [HomeController::history()](../app/Http/Controllers/HomeController.php) |
| Data | [RateService::history()](../app/Services/RateService.php) |

### Parameter

| Param | Lagbe? | Value |
|---|---|---|
| `metal` | Haa | `gold` / `silver` |
| `karat` | Haa | `22K`, `21K`, `18K`, `Traditional` ... |
| `range` | Na (default `30`) | `1`, `7`, `30`, `90`, `180`, `365`, `all` (din) |

Udahoron: `/history-data?metal=gold&karat=22K&range=30`

### Process

1. Purity khuje. Na pele ba lukano hole (24K) `{"rows":[],"total":0}` dey.
2. Latest record er date theke `range` din pichone porjonto row ane. Ar ekta purono row o ane, jate shobcheye purono row er o `change` hisab kora jay.
3. Protiti row er `change` = oi diner dam − ager row er dam.

### Response

```json
{
  "rows": [
    {"date": "Nov 21, 2026", "iso": "2026-11-21", "price": 17245, "change": 0},
    {"date": "Nov 20, 2026", "iso": "2026-11-20", "price": 17245, "change": -1410}
  ],
  "total": 42
}
```

`total` = oi purity er database e mot koto row ache. Browser e metal|karat|range onujayi result cache thake. Notun dam ashle cache clear hoy.

---

## 8. Nijer API — `POST /estimates`

**Kaj:** Calculator e "Save" korle hisab database e rakhe. Admin → Estimates e dekha jay.

| | |
|---|---|
| Route | `POST /estimates` (name `estimates.store`) |
| Controller | [HomeController::saveEstimate()](../app/Http/Controllers/HomeController.php) |
| Limit | `throttle:20,1` |
| Security | `X-CSRF-TOKEN` header lagbe (page er `<meta name="csrf-token">` theke) |

### Request

```json
{"karat": "22K", "weight_grams": 10, "making_percent": 12}
```

### Process

1. Validate: `weight_grams` 0.001–100000, `making_percent` 0–100.
2. **Dam browser theke nei na.** Server database theke oi karat er current dam niye nije abar hisab kore, jate keu vul dam pathate na pare:
   ```
   base   = weight × rate
   making = base × making% / 100
   vat    = (base + making) × vat_percent / 100      (settings, default 5%)
   total  = base + making + vat
   ```
3. Visitor cookie theke visitor khuje `saved_estimates` e save kore, IP soho.

### Response

```json
{"message": "Estimate saved.", "id": 15, "total": 203500.5}
```

Karat na mille `422 {"message": "Unknown purity."}`.

---

## 9. Server er dorkar (setup)

### HTTPS certificate (WAMP) — gurutto purno

Server goldr.org e HTTPS e request kore. PHP er kache CA certificate bundle na thakle sob fetch fail kore:

```
cURL error 60: SSL certificate problem: unable to get local issuer certificate
```

2026-11-20 e ei karone dam update bondho chilo. Ja kora hoyeche:

1. Mozilla CA bundle download: `https://curl.se/ca/cacert.pem` → `D:\wamp\bin\php\php8.3.14\extras\ssl\cacert.pem`
2. **Apache er** PHP ini (`D:\wamp\bin\php\php8.3.14\phpForApache.ini`) e:
   ```ini
   curl.cainfo = "D:/wamp/bin/php/php8.3.14/extras/ssl/cacert.pem"
   openssl.cafile = "D:/wamp/bin/php/php8.3.14/extras/ssl/cacert.pem"
   ```
   Backup: `phpForApache.ini.bak-20261121`
3. WAMP → Restart All Services.

> ⚠️ Terminal er `php` (XAMPP, `D:\IDB-WDPF\PHP\Xampp\php`) ar Apache er PHP (WAMP 8.3.14) **alada**. Terminal e `rates:fetch` kaj korle-o website e fail korte pare. Test korte Apache er ini diye chalan:
> ```bash
> D:/wamp/bin/php/php8.3.14/php.exe -c D:/wamp/bin/php/php8.3.14/phpForApache.ini artisan rates:fetch --force
> ```
> WAMP e PHP version bodlale notun version er `phpForApache.ini` e abar ei 2 line dite hobe.

Live hosting (cPanel ityadi) e sadharonoto CA bundle age thekei thake.

### Scheduler (optional)

Cron na thakle shudhu visitor ashle fetch hoy. Sob somoy update chaile proti minute e `php artisan schedule:run` chalate hobe. Setup: [LIVE_RATES.md § 6a](LIVE_RATES.md).

### External script / iframe

Page e kono **Content-Security-Policy** header nei, tai widget ar iframe kaj kore. Porobortite CSP dile ei domain gulo allow korte hobe:

| Directive | Domain |
|---|---|
| `script-src` | `https://www.goldr.org`, `https://cdn.jsdelivr.net` |
| `connect-src` | `https://www.goldr.org` (chart2.json) |
| `frame-src` | `https://s.tradingview.com` |

---

## 10. Somossa hole

| Lokkhon | Karon / Somadhan |
|---|---|
| "Updated:" purono date dekhay, admin log e `cURL error 60` | Apache PHP e CA bundle nei. § 9 dekhun |
| "Updated:" purono, kono log o nei | Keu page khole ni, ar cron nei. Admin → "Fetch now" chapun ba scheduler setup korun |
| Live market chart (goldr) khali | Browser console (F12) e `GoldrWidget Error` ba `ApexCharts is not defined` dekhun. ApexCharts er script `widget.js` er age ache kina, ar `goldr-widget-container` id thik ache kina dekhun |
| TradingView chart khali / "refused to connect" | TradingView `goldprice` embed bondho korte pare ba CSP `frame-src` block korche. § 5 er shesh point dekhun |
| `/rates/live` 429 error | Throttle (30/min). Ek IP theke onek tab khola. Swabhabik, porer bar thik hobe |
| `/estimates` 419 error | CSRF token purono. Page refresh korun |
| Silver card e sell-back nei | goldr.org silver er sell-back dam dey na. Card e column lukano thake |

---

## 11. Seema ar jhuki

- **Kono ta-i official public API na.** goldr.org er `price.js`, `widget.js`, `chart2.json` ar TradingView er `goldprice` embed sobai onno site er jonno banano. Format bodlate pare, bondho hote pare. Commercial bhabe chalanor age tader terms dekhun ba permission nin.
- Source credit (goldr.org, TradingView) page e deya ache. Rakhben.
- Kono external jinish fail korle site bhange na. Live dam fail hole shesh save kora dam dekhay. Widget/iframe fail hole shudhu oi box khali thake.
