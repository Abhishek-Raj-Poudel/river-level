# River Level

Laravel application for tracking river levels, seeded from Nepal's Department of Hydrology and Meteorology (DHM) realtime stream data.

## Setup

On a fresh checkout, install PHP dependencies before running any Artisan command:

```bash
composer install
```

If `vendor/autoload.php` is missing, Laravel commands like `php artisan db:seed` will fail until Composer dependencies are installed.

## Scraper-backed Seeder

The seeder that scrapes external data is [`database/seeders/RiverLevelSeeder.php`](/home/abhi/Projects/river-level/database/seeders/RiverLevelSeeder.php). It is invoked by [`database/seeders/DatabaseSeeder.php`](/home/abhi/Projects/river-level/database/seeders/DatabaseSeeder.php) when you run:

```bash
php artisan db:seed
```

### Data Source

`RiverLevelSeeder` uses [`app/Services/DhmScraperService.php`](/home/abhi/Projects/river-level/app/Services/DhmScraperService.php), which fetches and parses:

- `https://dhm.gov.np/hydrology/realtime-stream`

The scraper reads rows from the page's `table#tablegeneral` HTML table and extracts:

- basin
- station index
- station name
- district
- water level
- discharge

### What The Seeder Does

- Calls `DhmScraperService::fetch()` to scrape the DHM realtime stream page.
- Skips rows missing `basin` or `water_level`.
- Creates or updates `river_levels` records keyed by `slug`.
- Uses scraped values for `current_water_level`, `station_name`, and `district`.
- Fills several other fields with generated placeholder values such as `normal_water_level`, `length`, `temperature`, and coordinates.
- Falls back to an empty in-code fallback dataset if the scrape returns no usable rows.

### Notes

- [`database/seeders/DhmRiverLevelSeeder.php`](/home/abhi/Projects/river-level/database/seeders/DhmRiverLevelSeeder.php) exists but is currently empty and is not used by `DatabaseSeeder`.
- The scraper service is also used elsewhere in the app to refresh live river data, but the seeding path goes through `RiverLevelSeeder`.
