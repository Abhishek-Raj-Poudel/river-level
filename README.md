# River Level

River Level is Laravel 12 + Inertia React app for monitoring river stations in Nepal.
Main data source is Department of Hydrology and Meteorology (DHM).

This README is written as re-entry doc for future-you:
- what app does
- how data moves
- where important code lives
- what is placeholder vs real
- how to run project without relearning codebase

## Stack

- PHP 8.4
- Laravel 12
- Inertia.js v2 + React 19 + TypeScript
- Filament v4 for admin
- MySQL in Docker setup
- Laravel Reverb for broadcasting
- Guzzle + Symfony DomCrawler for scraping

## What App Does

App has 2 main surfaces:

1. Public river monitoring UI
   - Homepage lists river cards
   - Clicking card opens river detail page
   - Detail page shows measurements, coordinates, station info, elevation, link to source page

2. Filament admin panel
   - Admin path is `/admin`
   - Used to manage `RiverLevel` records and users

## Main Routes

Defined in [routes/web.php](/home/abhi/Projects/river-level/routes/web.php):

- `/` -> homepage, handled by `RiverController@index`
- `/river/{river}` -> river detail page, handled by `RiverController@show`
- `/admin` -> Filament admin panel
- authenticated routes also exist for dashboard, alerts, and user location

`RiverLevel` uses slug route model binding via `getRouteKeyName()`, so `/river/{river}` resolves by slug, not UUID.

## Important Models

### `RiverLevel`

File: [app/Models/RiverLevel.php](/home/abhi/Projects/river-level/app/Models/RiverLevel.php)

This is core model for monitored stations.

Important fields:
- `name`: basin / river name
- `station_name`: DHM station name
- `district`
- `current_water_level`
- `normal_water_level`
- `status`
- `station_link`: DHM station detail page URL
- `elevation`: scraped from station detail page when available
- `elevation_checked_at`: last time app attempted elevation scrape
- `last_updated`

### `User`

File: [app/Models/User.php](/home/abhi/Projects/river-level/app/Models/User.php)

User model implements Filament panel access. Current setup allows authenticated users into Filament admin panel.
If stricter admin-only access is needed later, add role field or `is_admin` column and update `canAccessPanel()`.

## How Data Flows

There are 2 different scrape flows.

### 1. Homepage data refresh from DHM realtime table

Core file: [app/Services/DhmScraperService.php](/home/abhi/Projects/river-level/app/Services/DhmScraperService.php)

`DhmScraperService::fetch()` scrapes:

- `https://dhm.gov.np/hydrology/realtime-stream`

It parses `table#tablegeneral` and extracts:
- basin
- station index
- station name
- district
- water level
- discharge
- station link

Then `RiverController@index`:
- caches raw DHM data for 15 minutes
- updates existing `river_levels` records from scraped data
- loads `RiverLevel::with('recentMeasurements')`
- renders homepage

Relevant file:
- [app/Http/Controllers/RiverController.php](/home/abhi/Projects/river-level/app/Http/Controllers/RiverController.php)

### 2. Elevation scrape from station detail page

When user opens river detail page, app may scrape station page for elevation.

Current behavior:
- only tries if `station_link` exists
- only tries if `elevation` is `null`
- only retries if `elevation_checked_at` is empty or older than 1 day
- if numeric elevation found, updates `elevation`
- whether found or not, updates `elevation_checked_at`

This prevents scraping on every click.

Elevation source page looks like:
- `https://dhm.gov.np/hydrology/hms-Single/{id}`

Current extraction order:
1. exact DHM XPath on `.list.list-03`
2. page JSON blob inside `var river = '...'`
3. regex fallback

Important files:
- [app/Services/DhmScraperService.php](/home/abhi/Projects/river-level/app/Services/DhmScraperService.php)
- [app/Http/Controllers/RiverController.php](/home/abhi/Projects/river-level/app/Http/Controllers/RiverController.php)

## Seeder Behavior

Main seeder:
- [database/seeders/RiverLevelSeeder.php](/home/abhi/Projects/river-level/database/seeders/RiverLevelSeeder.php)

It uses DHM realtime data to seed `river_levels`.

Important note:
- some fields are real from DHM
- some fields are still generated placeholder values

Real-ish / scraped:
- `station_name`
- `district`
- `current_water_level`
- `station_link`
- `elevation` if present in scraper payload

Generated / placeholder:
- `length`
- `temperature`
- parts of coordinates
- flow rate approximations
- `normal_water_level`
- some description text

Meaning:
- app is partly real-time
- DB is not yet fully normalized or fully sourced from official structured data

## Frontend Structure

Important files:

- [resources/js/pages/welcome.tsx](/home/abhi/Projects/river-level/resources/js/pages/welcome.tsx)
  - homepage entry
  - reloads `rivers` props on mount so card values stay fresh after returning from detail page

- [resources/js/components/river-list.tsx](/home/abhi/Projects/river-level/resources/js/components/river-list.tsx)
  - homepage filters and cards grid

- [resources/js/components/river-card.tsx](/home/abhi/Projects/river-level/resources/js/components/river-card.tsx)
  - summary card UI

- [resources/js/pages/river-detail.tsx](/home/abhi/Projects/river-level/resources/js/pages/river-detail.tsx)
  - detail page UI

- [resources/js/types/index.d.ts](/home/abhi/Projects/river-level/resources/js/types/index.d.ts)
  - shared TS types

## Filament Admin

Filament panel provider:
- [app/Providers/Filament/AdminPanelProvider.php](/home/abhi/Projects/river-level/app/Providers/Filament/AdminPanelProvider.php)

Current panel config:
- path: `/admin`
- login enabled
- resources auto-discovered from `app/Filament/Resources`

Important Filament resources:
- `RiverLevels`
- `Users`

If Filament login returns `403`, check `User::canAccessPanel()` first.

## Realtime / Alerts

App uses events + listener flow for flood alerts.

Important files:
- [app/Events/RiverLevelExceeded.php](/home/abhi/Projects/river-level/app/Events/RiverLevelExceeded.php)
- [app/Listeners/SendRiverFloodAlertNotification.php](/home/abhi/Projects/river-level/app/Listeners/SendRiverFloodAlertNotification.php)
- [app/Notifications/RiverFloodAlert.php](/home/abhi/Projects/river-level/app/Notifications/RiverFloodAlert.php)
- [app/Providers/AppServiceProvider.php](/home/abhi/Projects/river-level/app/Providers/AppServiceProvider.php)

`RiverLevel` model currently also broadcasts and sends mail when current level exceeds normal level.

Note:
- event/broadcast/mail logic is in model `saved()` hook
- workable for now
- long-term better home might be service/action or domain event layer

## Local Setup

## 1. Install dependencies

```bash
composer install
npm install
```

## 2. Environment

Create local env if missing:

```bash
cp .env.example .env
php artisan key:generate
```

Set database credentials in `.env`.

## 3. Run migrations

```bash
php artisan migrate
```

If you want seed data:

```bash
php artisan db:seed
```

## 4. Start app

Recommended dev command:

```bash
composer run dev
```

That starts:
- Laravel server
- queue worker
- logs via Pail
- Vite dev server

If you want separate processes instead:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

## Docker Setup

Docker compose file:
- [docker-compose.yml](/home/abhi/Projects/river-level/docker-compose.yml)

Services:
- `mysql`
- `app`
- `queue`
- `reverb`

Basic usage:

```bash
docker compose up --build
```

App URLs:
- app: `http://localhost:8000`
- reverb: `http://localhost:8080`

Docker env file:
- `.env.docker`

Current compose setup auto-runs migrations inside app container via `RUN_MIGRATIONS=true`.

## Useful Commands

Install deps:

```bash
composer install
npm install
```

Run app:

```bash
composer run dev
```

Run tests:

```bash
composer test
```

Type check frontend:

```bash
npm run types
```

Lint frontend:

```bash
npm run lint
```

Format frontend:

```bash
npm run format
```

List users quickly:

```bash
php artisan tinker
App\Models\User::select('id', 'name', 'email')->get();
```

## Known Caveats

### 1. Seeder still mixes real data and fake data

Many seeded river fields are placeholders. Good enough for UI development, not perfect for production truth.

### 2. DHM HTML can change

Scrapers depend on current DHM markup. If DHM changes table structure or station page structure, scrape logic in `DhmScraperService` will need adjustment.

### 3. Elevation scrape is lazy

Elevation is not bulk-fetched for all rivers on homepage load.
It is discovered when a river detail page is opened, then cached in DB.

### 4. Homepage freshness uses Inertia reload

Homepage reloads `rivers` props on mount to show fresh values after detail page updates.

### 5. Filament access is permissive right now

Current `User::canAccessPanel()` returns `true`.
Good for local/admin bootstrapping, not good enough for production role separation.

## Important Files To Re-Open First Next Time

If you come back later and need fast orientation, start here:

1. [README.md](/home/abhi/Projects/river-level/README.md)
2. [app/Http/Controllers/RiverController.php](/home/abhi/Projects/river-level/app/Http/Controllers/RiverController.php)
3. [app/Services/DhmScraperService.php](/home/abhi/Projects/river-level/app/Services/DhmScraperService.php)
4. [app/Models/RiverLevel.php](/home/abhi/Projects/river-level/app/Models/RiverLevel.php)
5. [database/seeders/RiverLevelSeeder.php](/home/abhi/Projects/river-level/database/seeders/RiverLevelSeeder.php)
6. [app/Providers/Filament/AdminPanelProvider.php](/home/abhi/Projects/river-level/app/Providers/Filament/AdminPanelProvider.php)
7. [resources/js/pages/welcome.tsx](/home/abhi/Projects/river-level/resources/js/pages/welcome.tsx)
8. [resources/js/pages/river-detail.tsx](/home/abhi/Projects/river-level/resources/js/pages/river-detail.tsx)

## Next Cleanup Ideas

When you return, likely useful next tasks:

- remove or rename legacy `scrape_link` if no longer needed
- stop using placeholder seeded fields where real data can replace them
- add proper admin role/permission model for Filament
- move alert side effects out of model hook into cleaner service/event flow
- add tests for station elevation scraping and controller retry behavior
