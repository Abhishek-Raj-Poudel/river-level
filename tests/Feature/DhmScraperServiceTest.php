<?php

use App\Models\RiverLevel;
use App\Services\DhmScraperService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Build a row shaped like DhmScraperService::mapLiveStation() output.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function liveStationRow(array $overrides = []): array
{
    return array_merge([
        'index' => 236,
        'dhm_station_id' => 236,
        'basin' => 'Bagmati',
        'station_index' => '581',
        'station_name' => 'Bagmati River at Bhorleni',
        'district' => 'Makawanpur',
        'water_level' => '2.709',
        'discharge' => null,
        'station_link' => null,
        'elevation' => null,
        'warning_level' => '5.2',
        'danger_level' => '6.2',
        'status' => 'normal',
        'lat' => 27.36,
        'lng' => 85.47,
        'measured_at' => '2026-10-14T16:45:00+00:00',
    ], $overrides);
}

it('creates a river level and measurement from a live station row', function () {
    $result = app(DhmScraperService::class)->updateOrCreateRiverLevels([liveStationRow()]);

    expect($result['created'])->toBe(1)
        ->and($result['errors'])->toBeEmpty();

    $river = RiverLevel::firstWhere('dhm_station_id', 236);

    expect($river)->not->toBeNull()
        ->and($river->station_name)->toBe('Bagmati River at Bhorleni')
        ->and($river->district)->toBe('Makawanpur')
        ->and($river->basin)->toBe('Bagmati')
        ->and($river->slug)->toBe('bagmati-river-at-bhorleni')
        ->and((float) $river->current_water_level)->toBe(2.709);

    $measurement = $river->measurements()->first();

    expect($measurement)->not->toBeNull()
        ->and($measurement->measured_at->toIso8601String())->toBe('2026-10-14T16:45:00+00:00');
});

it('matches an existing river by DHM station id when the name changes', function () {
    $scraper = app(DhmScraperService::class);
    $scraper->updateOrCreateRiverLevels([liveStationRow()]);

    $result = $scraper->updateOrCreateRiverLevels([
        liveStationRow([
            'station_name' => 'Bagmati River at Bhorleni (renamed)',
            'water_level' => '3.100',
        ]),
    ]);

    expect($result['created'])->toBe(0)
        ->and($result['updated'])->toBe(1)
        ->and(RiverLevel::count())->toBe(1);

    $river = RiverLevel::firstWhere('dhm_station_id', 236);

    expect((float) $river->current_water_level)->toBe(3.1);
});

it('backfills the DHM station id on an existing river matched by district and name', function () {
    RiverLevel::create([
        'name' => 'Bagmati River at Bhorleni',
        'station_name' => 'Bagmati River at Bhorleni',
        'district' => 'Makawanpur',
        'country' => 'Nepal',
        'continent' => 'Asia',
        'length' => 0,
        'current_water_level' => 1.0,
        'normal_water_level' => 5.2,
        'status' => 'normal',
        'current_flow_rate' => 0,
        'average_flow_rate' => 0,
        'temperature' => 0,
        'lat' => 27.36,
        'lng' => 85.47,
        'description' => '',
        'last_updated' => now(),
    ]);

    app(DhmScraperService::class)->updateOrCreateRiverLevels([liveStationRow()]);

    expect(RiverLevel::count())->toBe(1)
        ->and(RiverLevel::first()->dhm_station_id)->toBe(236)
        ->and(RiverLevel::first()->basin)->toBe('Bagmati');
});

it('persists a station the feed reports without a district', function () {
    $result = app(DhmScraperService::class)->updateOrCreateRiverLevels([
        liveStationRow([
            'dhm_station_id' => 5696,
            'station_name' => 'Gudung at EW Highway',
            'district' => '',
        ]),
    ]);

    expect($result['created'])->toBe(1)
        ->and(RiverLevel::firstWhere('dhm_station_id', 5696))->not->toBeNull();
});

it('gives same-named stations in different districts distinct slugs', function () {
    $result = app(DhmScraperService::class)->updateOrCreateRiverLevels([
        liveStationRow([
            'dhm_station_id' => 1,
            'station_name' => 'Sunkoshi River',
            'district' => 'Sindhupalchok',
        ]),
        liveStationRow([
            'dhm_station_id' => 2,
            'station_name' => 'Sunkoshi River',
            'district' => 'Dolakha',
        ]),
    ]);

    expect($result['created'])->toBe(2);

    $slugs = RiverLevel::orderBy('dhm_station_id')->pluck('slug')->all();

    expect($slugs)->toHaveCount(2)
        ->and(array_unique($slugs))->toHaveCount(2);
});

it('skips rows with an invalid water level', function () {
    $result = app(DhmScraperService::class)->updateOrCreateRiverLevels([
        liveStationRow(['water_level' => '-9999']),
        liveStationRow(['dhm_station_id' => 999, 'water_level' => '']),
    ]);

    expect($result['created'])->toBe(0)
        ->and(RiverLevel::count())->toBe(0);
});
