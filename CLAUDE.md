# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

- **Laravel 8.40** on **PHP 7.3+/8.0** (see `composer.json`).
- **MySQL** (`nusa.logistia.it` database) accessed almost exclusively through the `DB` facade — Eloquent is **not** used in application code. The only `App\Models\User` is the stock Laravel scaffold and is unused.
- **Laravel Mix** (not Vite). Entry points: `resources/js/app.js`, `resources/css/app.css` → `public/js`, `public/css`.
- **Local stack**: XAMPP on Windows. The repo is served from `c:\xampp81\htdocs\dashboard\progetti\nusa_logistia.it`.
- **Domain language is Italian**. All table names, columns, route segments, view names, and form fields are in Italian (`utenti`, `aziende`, `mezzi`, `cantieri`, `ordini_trasporto`, `documenti_trasporto`/DDT, `tariffari`, `dispositivi_tracking`, `posizioni_live`, …). Keep new code consistent.

## Common commands

```bash
# PHP deps (run after pulling composer.json changes)
composer install

# Frontend deps and build
npm install
npm run dev           # one-off compile
npm run watch         # rebuild on save
npm run prod          # production build

# Laravel
php artisan serve     # http://localhost:8000 (or browse via XAMPP at /dashboard/progetti/nusa_logistia.it/public)
php artisan tinker
php artisan route:list
php artisan config:clear && php artisan view:clear

# Tests (PHPUnit; tests/Feature and tests/Unit are essentially empty scaffolds)
vendor/bin/phpunit
vendor/bin/phpunit --filter SomeTestName
```

## Architecture

### Three user areas, one app

URL prefix decides the area; **routing-level middleware does not protect them** (see auth section).

- **`admin/*`** — super-admin / multi-tenant management (creates `aziende` and their first user). Views: `resources/views/admin/`. Controller: `AdminController`.
- **`azienda/*`** — main tenant UI: fleet, drivers, transport orders, DDT, tariffs, analytics, planning, GPS tracking dashboard, magazzino, cantieri, …. Views: `resources/views/azienda/`. Several controllers split by feature: `AziendaController` (5800+ lines, the bulk of CRUD), `TrasportiController` (TMS — orders/DDT/tariffs/centro operativo), `AnalyticsController`, `PlanningController`, `FlottaController` (FlottaInCloud integration), `TrackingDashboardController`.
- **`autista/*`** — driver mobile interface (dashboard, consegne, navigatore, POD, rifornimenti). Views: `resources/views/autista/`. Controller: `AutistaController`.
- **`resources/views/default/`** — a wider ERP surface (bandi, preventivi, fatture, commesse, ODL, magazzino, contratti, …) used from `azienda/*` actions; many of these blades exist but only parts are wired up via current routes.

The single user table `utenti` carries the role: `admin_azienda IN (1,2)` lands users on `azienda/index`, otherwise `admin/index`. **At login time** (`AdminController@login`), if the user has an active row in `dispositivi_tracking`, they are redirected to `autista/dashboard` instead — that is the only thing that turns a user into a driver.

### Authentication — custom, not Laravel Auth

Do not reach for `Auth::`, `auth()`, the `auth` middleware, or `App\Models\User`. The actual pattern:

- Login at `AdminController@login` runs `DB::select('SELECT * FROM utenti WHERE email = "…" AND password = "…"')` against the **plaintext** `password` column and stuffs the row into `session(['utente' => $utente])`.
- Every protected action calls `$this->is_loggato()` at the top, which is a per-controller helper (defined in `AdminController`, `AziendaController`, `TrasportiController`, `AutistaController`, `PlanningController`, `AnalyticsController`, `AjaxController`, `StampaController`) that does `if (!session()->has('utente')) Redirect::to('admin/login')->send();`.
- `AutistaController` and `FlottaController` instead enforce this in their constructor via `$this->middleware(...)` — same effect.
- **CSRF is disabled**: `\App\Http\Middleware\VerifyCsrfToken::class` is commented out in `App\Http\Kernel::$middlewareGroups['web']`. Forms in this app do not include `@csrf`. Don't "fix" this without coordinating — many existing forms will start failing.
- Session lifetime is set to `525600` minutes in `.env` (one year) so login is effectively persistent.

When you add a new action, copy the existing pattern: `$this->is_loggato(); $utente = session('utente');` then scope every query by `$utente->id_azienda`.

### Multi-tenancy by `id_azienda`

Every domain table has an `id_azienda` column and queries are tenant-scoped manually:

```php
DB::table('ordini_trasporto')->where('id_azienda', $utente->id_azienda)…
```

There is no global scope, no policy, no middleware enforcing this. **Forgetting the `id_azienda` filter leaks data across tenants** — always include it on reads, writes, and joins of `aziende`-scoped tables (`utenti`, `mezzi`, `cantieri`, `clienti`, `ordini_trasporto`, `documenti_trasporto`, `tariffari`, `dispositivi_tracking`, `posizioni_live`, `posizioni_storico`, `km_giornalieri`, etc.).

### Schema lives in MySQL, not in migrations

`database/migrations/` contains a **single** file (`2026_03_20_000001_add_fish_to_utenti_table.php`). The real schema is managed in the live MySQL database; `php artisan migrate:fresh` will not reproduce it. To inspect tables, query MySQL directly (e.g. `phpMyAdmin` via XAMPP). When adding a column, write a Laravel migration **and** apply it to the dev DB; do not assume the schema can be rebuilt from this repo alone.

### Controller conventions

Controllers are very large monolithic CRUD hubs (e.g. `AziendaController` ≈ 5800 lines, `AutistaController` ≈ 2600, `TrasportiController` ≈ 2200). The repeated pattern in a section action is:

```php
public function ordiniTrasporto(Request $request) {
    $this->is_loggato();
    $utente = session('utente');

    if ($request->isMethod('post')) {
        $dati = $request->all();
        if (isset($dati['crea_ordine']))      $this->creaOrdine($dati, $utente);
        elseif (isset($dati['modifica_ordine'])) $this->modificaOrdine($dati, $utente);
        elseif (isset($dati['elimina_ordine'])) $this->eliminaOrdine($dati, $utente);
        return redirect('/azienda/ordini-trasporto')->with('success', '…');
    }

    // …build query, return view('azienda.ordini_trasporto', compact(...))
}
```

The branch is selected by **named hidden inputs** in the blade form (`<input type="hidden" name="crea_ordine">`), not by separate routes. When adding a new operation, add a new `name=` flag and a private `creaXxx/modificaXxx/eliminaXxx` helper rather than introducing a new route or REST verb. Many older routes are still registered as `Route::any(..., 'Controller@method')` strings.

### Frontend / views

- Blade only; no SPA. Layout/partials live in `resources/views/{admin,azienda,autista,default}/common/`.
- Page assets are pulled from `public/default/...` (a vendor admin theme) and bespoke pages often inline their own JS. Many forms post back to themselves and rely on `session()->flash` (`->with('success', …)`) for feedback.

### TMS / GPS subsystem

- **Driver tablet → server** uses `TrackingApiController` (`/api/tracking/*` and duplicates under `/api/tracking/*` in `routes/web.php`): `setup`, `position`, `batch`, `status`. A device is identified by a 64-char `device_token` stored in `dispositivi_tracking`. Positions go to `posizioni_live` (one row per device) + `posizioni_storico` (append-only) and aggregated daily into `km_giornalieri`.
- **Office dashboard** uses `TrackingDashboardController` and the `azienda/tracking/*` routes (live map, KM report, device CRUD, mezzo association).
- **FlottaInCloud** integration (`FlottaController`, base URL `https://api.flottaincloud.it/external_api/v1/`) is a per-tenant external GPS provider. Credentials are stored in `aziende.flotta_email` / `flotta_token` / `flotta_abilitato`. Always check `hasFlottaInCloudEnabled()` before calling.
- **Routing/Distance** uses Google Maps (`TrasportiController@calcolaKm`/`calcolaKmGoogle`) — the API key currently lives in the controller, not `.env`.

### Files, exports, integrations

- **File uploads** (DDT signatures, allegati cantieri, scontrini, allegati bandi/decreti/clienti/fatture/preventivi, …) are written directly under `public/allegati_*` with random `Str::random()` filenames. They are publicly served because they live in `public/`.
- **Public DDT download** uses a per-DDT token route `/ddt/download/{token}` (`AutistaController@ddtPdfPubblico`) — the only way to expose a PDF without login.
- **Excel I/O** via `maatwebsite/excel`: imports in `app/Imports/*` (Articoli, BOM, BP, Magazzino, Storico, Vendite, Tariffe), exports in `app/Exports/*`.
- **PDFs** via `mpdf/mpdf` (DDT, report TMS, analytics, responsabili).
- **Email** via `phpmailer/phpmailer` (driver-app DDT email, notifications). IMAP via `webklex/laravel-imap`.
- **Barcodes** via `ayeo/gs1_barcode` and `nextgen-tech/gs1-decoder`.
- **SFTP** via `phpseclib3` (used by some imports/exports).
- **OpenAPI Italia** P.IVA lookup proxied through `AdminController@cercaPiva` — the bearer token is hardcoded in that method, refresh it there if calls start returning 401.

### Cross-cutting integration points

- `PlanningController::isAutistaBloccato($idAutista, $data, $idAzienda)` — static helper called from `TrasportiController` when creating/editing orders to warn if the driver is on rest. Keep the static signature stable.
- `AdminController@login` is the **only** login entry; `azienda/login` is referenced by some redirects (e.g. `FlottaController`) but routes them back through `admin/login`.
- `routes/api.php` and `routes/web.php` both register the same `/api/tracking/*` endpoints — the web copy is what the tablet historically targets. Don't remove either without checking the deployed APK.

## Things to be careful about

- **Do not enable CSRF or Laravel Auth globally** without auditing every form and `Route::any`. The codebase assumes neither is on.
- **Never run `php artisan migrate:fresh` against a real DB** — there are no migrations for almost any table.
- **Always scope by `id_azienda`** on any query touching tenant data.
- The codebase uses `Route::any(...)` and `$request->isMethod('post')` widely — keep GET handlers idempotent and read-only because the same URL also renders the page.
- Italian variable/column names are the convention; do not rename to English in passing.
