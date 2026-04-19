<?php

namespace App\Services;

use App\Models\RiverLevel;
use App\Models\RiverMeasurement;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class DhmScraperService
{
    protected string $url = 'https://dhm.gov.np/hydrology/realtime-stream';
    /* protected string $url = 'https://hydrology.gov.np/#/river_watch?_k=pprrmt'; */

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
     * Update RiverLevel records with scraped DHM data
     * Matches records by district and station_name
     */
    public function updateRiverLevels(array $dhmData): array
    {
        $updated = 0;
        $errors = [];

        foreach ($dhmData as $station) {
            try {
                // Skip entries with missing required data
                if (empty($station['district']) || empty($station['station_name']) || empty($station['water_level'])) {
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

                if ($riverLevel) {
                    // Update the record with scraped data (without triggering events)
                    $riverLevel->withoutEvents(function () use ($riverLevel, $station) {
                        $currentLevel = (float) $station['water_level'];

                        // Check if we already have a measurement for today
                        $today = Carbon::now()->toDateString();
                        $existingMeasurement = RiverMeasurement::where('river_level_id', $riverLevel->id)
                            ->whereDate('measured_at', $today)
                            ->first();

                        // Only add measurement if we don't have one for today
                        if (! $existingMeasurement) {
                            RiverMeasurement::create([
                                'river_level_id' => $riverLevel->id,
                                'water_level' => $currentLevel,
                                'measured_at' => Carbon::now(),
                            ]);

                            // Clean up old measurements (keep only last 30 days)
                            RiverMeasurement::where('river_level_id', $riverLevel->id)
                                ->where('measured_at', '<', Carbon::now()->subDays(30))
                                ->delete();
                        }

                        $riverLevel->update([
                            'current_water_level' => $currentLevel,
                            'current_flow_rate' => ! empty($station['discharge']) ? (float) $station['discharge'] : $riverLevel->current_flow_rate,
                            'elevation' => isset($station['elevation']) && is_numeric($station['elevation']) ? (float) $station['elevation'] : $riverLevel->elevation,
                            'station_link' => $station['station_link'] ?? $riverLevel->station_link,
                            'last_updated' => Carbon::now(),
                        ]);
                    });

                    Log::info('Updated river level from DHM data', [
                        'district' => $station['district'],
                        'station_name' => $station['station_name'],
                        'water_level' => $station['water_level'],
                        'discharge' => $station['discharge'] ?? 'unchanged',
                    ]);

                    $updated++;
                } else {
                    Log::warning('No matching RiverLevel record found for DHM data', [
                        'district' => $station['district'],
                        'station_name' => $station['station_name'],
                    ]);
                }
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
