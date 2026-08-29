<?php

namespace App\Services;

use App\Models\RiverLevel;
use App\Models\RiverMeasurement;
use Carbon\Carbon;
use ElephantIO\Client as ElephantClient;
use ElephantIO\Engine\SocketIO\Version4X;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class DhmScraperService
{
    /* protected string $url = 'https://dhm.gov.np/hydrology/realtime-stream'; */
    protected string $url = 'https://hydrology.gov.np/#/river_watch?_k=pprrmt';

    public function fetch(?string $url = null)
    {
        $scrapeUrl = $url ?? $this->url;

        try {
            Log::info('Fetching DHM river data...', ['url' => $scrapeUrl]);

            $client = new Client(['timeout' => 15, 'headers' => ['User-Agent' => 'RiverWatchScraper']]);
            $res = $client->get($scrapeUrl);
            $html = (string) $res->getBody();

            if (! $url) {
                Log::info($html); // Only log full HTML for main URL
            }

            $crawler = new Crawler($html);
            $rows = [];

            $crawler->filter('table#tablegeneral tr')->each(function (Crawler $tr, $i) use (&$rows) {
                $tds = $tr->filter('td');
                if ($tds->count() >= 7) {
                    $stationCell = $tds->eq(3);
                    $stationLink = null;

                    if ($stationCell->filter('a')->count() > 0) {
                        $stationLink = $this->normalizeUrl($stationCell->filter('a')->attr('href'));
                    }

                    $rows[] = [
                        'index' => trim($tds->eq(0)->text('')),
                        'basin' => trim($tds->eq(1)->text('')),
                        'station_index' => trim($tds->eq(2)->text('')),
                        'station_name' => trim($stationCell->text('')),
                        'district' => trim($tds->eq(4)->text('')),
                        'water_level' => trim($tds->eq(5)->text('')),
                        'discharge' => trim($tds->eq(6)->text('')),
                        'station_link' => $stationLink,
                        'elevation' => null,
                    ];
                }
            });

            Log::info('Fetched DHM data successfully', ['count' => count($rows), 'url' => $scrapeUrl]);

            return $rows;
        } catch (\Throwable $e) {
            Log::error('Error fetching DHM river data', ['message' => $e->getMessage(), 'url' => $scrapeUrl]);

            return [];
        }
    }

    public function fetchStationDetails(string $url): array
    {
        try {
            Log::info('Fetching DHM station details...', ['url' => $url]);

            $client = new Client(['timeout' => 15, 'headers' => ['User-Agent' => 'RiverWatchScraper']]);
            $res = $client->get($url);
            $html = (string) $res->getBody();

            $elevation = $this->extractElevationFromHtml($html);

            Log::info('Fetched DHM station details', [
                'url' => $url,
                'elevation' => $elevation,
            ]);

            return [
                'elevation' => $elevation,
            ];
        } catch (\Throwable $e) {
            Log::error('Error fetching DHM station details', [
                'message' => $e->getMessage(),
                'url' => $url,
            ]);

            return [
                'elevation' => null,
            ];
        }
    }

    /**
     * Fetch live river data via Socket.IO from hydrology.gov.np.
     *
     * The site renders data client-side over a Socket.IO v4 connection
     * (path /gss/socket.io). On connect we emit `client_request` with the
     * desired channel; the server then pushes the payload on a matching event.
     *
     * @param  string  $channel  One of: river_test (water levels), rainfall_watch, river_discharge
     * @return array<int, array<string, mixed>> Rows shaped like fetch() for updateRiverLevels()
     */
    public function fetchLiveData(string $channel = 'river_test'): array
    {
        try {
            Log::info('Fetching live DHM river data via Socket.IO', ['channel' => $channel]);

            $client = new ElephantClient(new Version4X('https://hydrology.gov.np', [
                'sio_path' => 'gss/socket.io',
                'version' => 4,
                'timeout' => 10,
                'transports' => ['websocket'],
                'transport' => 'websocket',
            ]));

            $client->connect();
            $client->emit('client_request', $channel);

            $rows = [];
            $start = microtime(true);

            while (microtime(true) - $start < 15) {
                $packet = $client->wait(null, 3);

                if ($packet === null) {
                    continue;
                }

                $event = is_array($packet->event)
                    ? ($packet->event[0] ?? null)
                    : $packet->event;

                if ($event !== $channel) {
                    continue;
                }

                foreach ((array) ($packet->data ?? []) as $item) {
                    if (is_array($item)) {
                        $rows[] = $this->mapLiveStation($item);
                    }
                }

                break;
            }

            $client->disconnect();

            Log::info('Fetched live DHM data successfully', [
                'channel' => $channel,
                'count' => count($rows),
            ]);

            return $rows;
        } catch (\Throwable $e) {
            Log::error('Error fetching live DHM river data', [
                'message' => $e->getMessage(),
                'channel' => $channel,
            ]);

            return [];
        }
    }

    /**
     * Normalize a single Socket.IO station payload into the row shape
     * consumed by updateRiverLevels().
     */
    protected function mapLiveStation(array $item): array
    {
        $waterLevel = $item['waterLevel']['value'] ?? null;

        $station = [
            'index' => $item['id'] ?? null,
            'basin' => $item['basin'] ?? null,
            'station_index' => $item['stationIndex'] ?? null,
            'station_name' => trim((string) ($item['name'] ?? '')),
            'district' => trim((string) ($item['district'] ?? '')),
            'water_level' => $waterLevel !== null ? (string) $waterLevel : '',
            'discharge' => null,
            'station_link' => null,
            'elevation' => $item['elevation'] ?? null,
            'warning_level' => $item['warning_level'] ?? null,
            'danger_level' => $item['danger_level'] ?? null,
            'status' => $item['status'] ?? null,
            'lat' => $item['latitude'] ?? null,
            'lng' => $item['longitude'] ?? null,
        ];

        $station['status'] = $this->mapStatus(
            $station['status'],
            $waterLevel !== null ? (float) $waterLevel : 0.0,
            $station['warning_level'],
        );

        return $station;
    }

    /**
     * Map the raw DHM status string to the frontend status enum
     * (low | normal | high | critical). Falls back to a threshold
     * comparison against the warning level when the label is missing.
     */
    protected function mapStatus(?string $raw, float $level, mixed $warningLevel = null): string
    {
        $label = strtoupper((string) $raw);

        if (str_contains($label, 'DANGER') || str_contains($label, 'CRITICAL')) {
            return 'critical';
        }

        // "BELOW WARNING LEVEL" must be normal, not high — check BELOW/NORMAL first.
        if (str_contains($label, 'BELOW') || str_contains($label, 'NORMAL')) {
            return 'normal';
        }

        if (str_contains($label, 'ABOVE') || str_contains($label, 'WARNING')) {
            return 'high';
        }

        if (str_contains($label, 'LOW')) {
            return 'low';
        }

        if (is_numeric($warningLevel) && $level > (float) $warningLevel) {
            return 'high';
        }

        return 'normal';
    }

    /**
     * A water level is only usable when it is a real, finite value. The DHM
     * feed reports "-9999" / "-9999977" (and sometimes empty strings) for
     * stations without data, which would otherwise overflow the measurements
     * decimal column or corrupt readings.
     */
    protected function isValidWaterLevel(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === '-') {
            return false;
        }

        if (! is_numeric($value)) {
            return false;
        }

        $level = (float) $value;

        return $level >= -100 && $level <= 999999;
    }

    /**
     * Update RiverLevel records with scraped DHM data
     * Matches records by district and station_name
     */
    public function updateRiverLevels(array $dhmData): array
    {
        $updated = 0;
        $errors = [];

        foreach ($dhmData as $station) {
            try {
                // Skip entries with missing required data or invalid water levels
                if (empty($station['district']) || empty($station['station_name']) || ! $this->isValidWaterLevel($station['water_level'] ?? null)) {
                    Log::warning('Skipping DHM data entry with missing required fields', [
                        'district' => $station['district'] ?? 'missing',
                        'station_name' => $station['station_name'] ?? 'missing',
                        'water_level' => $station['water_level'] ?? 'missing',
                    ]);

                    continue;
                }

                // Find existing RiverLevel record by district and station_name
                $riverLevel = RiverLevel::where('district', $station['district'])
                    ->where('station_name', $station['station_name'])
                    ->first();

                if (! $riverLevel) {
                    Log::warning('No matching RiverLevel record found for DHM data', [
                        'district' => $station['district'],
                        'station_name' => $station['station_name'],
                    ]);

                    continue;
                }

                // Update the record with scraped data (without triggering events)
                $riverLevel->withoutEvents(function () use ($riverLevel, $station) {
                    $this->applyLiveMeasurement($riverLevel, $station, (float) $station['water_level']);
                });

                Log::info('Updated river level from DHM data', [
                    'district' => $station['district'],
                    'station_name' => $station['station_name'],
                    'water_level' => $station['water_level'],
                    'discharge' => $station['discharge'] ?? 'unchanged',
                ]);

                $updated++;
            } catch (\Throwable $e) {
                $error = [
                    'district' => $station['district'] ?? 'unknown',
                    'station_name' => $station['station_name'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
                $errors[] = $error;
                Log::error('Error updating river level from DHM data', $error);
            }
        }

        Log::info('DHM data update completed', [
            'total_processed' => count($dhmData),
            'updated' => $updated,
            'errors' => count($errors),
        ]);

        return [
            'updated' => $updated,
            'errors' => $errors,
            'total_processed' => count($dhmData),
        ];
    }

    /**
     * Apply a live measurement to an existing RiverLevel.
     *
     * Creates today's measurement if missing (pruning older than 30 days) and
     * updates the current level, flow rate, elevation, link and timestamp.
     * Must be called inside a withoutEvents() closure to avoid alert spam.
     */
    protected function applyLiveMeasurement(RiverLevel $riverLevel, array $station, float $currentLevel): void
    {
        $today = Carbon::now()->toDateString();
        $existingMeasurement = RiverMeasurement::where('river_level_id', $riverLevel->id)
            ->whereDate('measured_at', $today)
            ->first();

        if (! $existingMeasurement) {
            RiverMeasurement::create([
                'river_level_id' => $riverLevel->id,
                'water_level' => $currentLevel,
                'measured_at' => Carbon::now(),
            ]);

            RiverMeasurement::where('river_level_id', $riverLevel->id)
                ->where('measured_at', '<', Carbon::now()->subDays(30))
                ->delete();
        }

        $riverLevel->update([
            'current_water_level' => $currentLevel,
            'current_flow_rate' => ! empty($station['discharge']) ? (float) $station['discharge'] : $riverLevel->current_flow_rate,
            'elevation' => isset($station['elevation']) && is_numeric($station['elevation']) ? (float) $station['elevation'] : $riverLevel->elevation,
            'station_link' => $station['station_link'] ?? $riverLevel->station_link,
            'status' => ! empty($station['status']) ? $station['status'] : $riverLevel->status,
            'normal_water_level' => isset($station['warning_level']) && is_numeric($station['warning_level']) ? (float) $station['warning_level'] : $riverLevel->normal_water_level,
            'last_updated' => Carbon::now(),
        ]);
    }

    /**
     * Fetch live data and update-or-create RiverLevel records.
     *
     * Unlike updateRiverLevels(), this creates a new RiverLevel when no match
     * is found by district + station_name. Intended for one-time live dumps
     * where the river_levels table may not be pre-seeded.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{updated: int, created: int, errors: array, total_processed: int}
     */
    public function updateOrCreateRiverLevels(array $rows): array
    {
        $updated = 0;
        $created = 0;
        $errors = [];

        foreach ($rows as $station) {
            try {
                if (empty($station['district']) || empty($station['station_name']) || ! $this->isValidWaterLevel($station['water_level'] ?? null)) {
                    Log::warning('Skipping live data entry with missing required fields', [
                        'district' => $station['district'] ?? 'missing',
                        'station_name' => $station['station_name'] ?? 'missing',
                        'water_level' => $station['water_level'] ?? 'missing',
                    ]);

                    continue;
                }

                $currentLevel = (float) $station['water_level'];

                $riverLevel = RiverLevel::where('district', $station['district'])
                    ->where('station_name', $station['station_name'])
                    ->first();

                if ($riverLevel) {
                    $riverLevel->withoutEvents(function () use ($riverLevel, $station, $currentLevel) {
                        $this->applyLiveMeasurement($riverLevel, $station, $currentLevel);
                    });

                    $updated++;

                    continue;
                }

                // No match: create a new RiverLevel (suppress events to avoid alert spam).
                // withoutEvents() also disables the model's creating callback, so we
                // generate the UUID id/slug manually here. The base table has several
                // NOT NULL columns unrelated to live data, so we seed safe defaults.
                RiverLevel::withoutEvents(function () use ($station, $currentLevel, &$created) {
                    $river = RiverLevel::create([
                        'id' => (string) Str::uuid(),
                        'slug' => Str::slug($station['station_name']),
                        'name' => $station['station_name'],
                        'country' => 'Nepal',
                        'continent' => 'Asia',
                        'length' => 0,
                        'current_water_level' => $currentLevel,
                        'normal_water_level' => isset($station['warning_level']) && is_numeric($station['warning_level']) ? (float) $station['warning_level'] : 0,
                        'current_flow_rate' => 0,
                        'average_flow_rate' => 0,
                        'temperature' => 0,
                        'status' => $station['status'] ?? 'UNKNOWN',
                        'lat' => $station['lat'] ?? 0,
                        'lng' => $station['lng'] ?? 0,
                        'description' => '',
                        'weekly_data' => json_encode([]),
                        'last_updated' => Carbon::now(),
                        'station_name' => $station['station_name'],
                        'district' => $station['district'],
                        'basin' => $station['basin'] ?? null,
                        'station_index' => $station['station_index'] ?? null,
                        'warning_level' => $station['warning_level'] ?? null,
                        'danger_level' => $station['danger_level'] ? (float) $station['danger_level'] : null,
                        'elevation' => isset($station['elevation']) && is_numeric($station['elevation']) ? (float) $station['elevation'] : null,
                    ]);

                    $this->applyLiveMeasurement($river, $station, $currentLevel);

                    $created++;
                });
            } catch (\Throwable $e) {
                $errors[] = [
                    'district' => $station['district'] ?? 'unknown',
                    'station_name' => $station['station_name'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
                Log::error('Error upserting live river level', end($errors));
            }
        }

        Log::info('Live river level upsert completed', [
            'updated' => $updated,
            'created' => $created,
            'errors' => count($errors),
        ]);

        return [
            'updated' => $updated,
            'created' => $created,
            'errors' => $errors,
            'total_processed' => count($rows),
        ];
    }

    private function normalizeUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return 'https://dhm.gov.np/'.ltrim($url, '/');
    }

    private function extractElevationFromHtml(string $html): ?float
    {
        $crawler = new Crawler($html);

        $xpaths = [
            "//ul[contains(@class,'list-03')]/li[h6[normalize-space()='Elevation']]/p",
            "//ul[contains(@class,'list-03')]/li[h6[normalize-space()='Altitude']]/p",
            "//*[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'elevation')]/following-sibling::*[1]",
            "//*[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'elevation')]/parent::*/*[last()]",
            "//*[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'altitude')]/following-sibling::*[1]",
            "//*[contains(translate(normalize-space(.), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'altitude')]/parent::*/*[last()]",
        ];

        foreach ($xpaths as $xpath) {
            try {
                $node = $crawler->filterXPath($xpath)->first();

                if ($node->count() > 0) {
                    $value = $this->extractNumericValue($node->text(''));

                    if ($value !== null) {
                        return $value;
                    }
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        if (preg_match('/var\s+river\s*=\s*\'([^\']+)\'\s*;/s', $html, $matches) === 1) {
            $json = html_entity_decode(stripslashes($matches[1]), ENT_QUOTES | ENT_HTML5);
            $riverData = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $riverData['elevation'] ?? null;

                if ($value !== null && $value !== '' && is_numeric((string) $value)) {
                    return (float) $value;
                }
            }
        }

        $fallbackPatterns = [
            '/elevation[^0-9\-]*(-?\d+(?:\.\d+)?)/i',
            '/altitude[^0-9\-]*(-?\d+(?:\.\d+)?)/i',
            '/elev\.[^0-9\-]*(-?\d+(?:\.\d+)?)/i',
        ];

        foreach ($fallbackPatterns as $pattern) {
            if (preg_match($pattern, strip_tags($html), $matches) === 1) {
                return (float) $matches[1];
            }
        }

        return null;
    }

    private function extractNumericValue(string $text): ?float
    {
        $normalized = preg_replace('/\s+/', ' ', trim($text));

        if (blank($normalized)) {
            return null;
        }

        if (preg_match('/-?\d+(?:\.\d+)?/', $normalized, $matches) !== 1) {
            return null;
        }

        return (float) $matches[0];
    }
}
