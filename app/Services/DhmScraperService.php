<?php

namespace App\Services;

use App\Models\RiverLevel;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
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
                    $rows[] = [
                        'index' => trim($tds->eq(0)->text('')),
                        'basin' => trim($tds->eq(1)->text('')),
                        'station_index' => trim($tds->eq(2)->text('')),
                        'station_name' => trim($tds->eq(3)->text('')),
                        'district' => trim($tds->eq(4)->text('')),
                        'water_level' => trim($tds->eq(5)->text('')),
                        'discharge' => trim($tds->eq(6)->text('')),
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
                        $weeklyData = $riverLevel->weekly_data ?? [];

                        // Check if we need to add current measurement to weekly data
                        $latestMeasurement = null;
                        if (! empty($weeklyData)) {
                            // Sort by datetime descending to get the latest
                            usort($weeklyData, function ($a, $b) {
                                return strtotime($b['datetime'] ?? '1970-01-01') <=> strtotime($a['datetime'] ?? '1970-01-01');
                            });
                            $latestMeasurement = $weeklyData[0];
                        }

                        // Add current measurement if latest is not from today
                        $today = Carbon::now()->toDateString();
                        $latestDate = isset($latestMeasurement['datetime'])
                            ? Carbon::parse($latestMeasurement['datetime'])->toDateString()
                            : null;

                        if ($latestDate !== $today) {
                            // Add current measurement and keep only last 7 entries
                            array_unshift($weeklyData, [
                                'datetime' => Carbon::now()->toISOString(),
                                'level' => $currentLevel,
                            ]);

                            // Keep only the most recent 7 measurements
                            $weeklyData = array_slice($weeklyData, 0, 7);
                        }

                        $riverLevel->update([
                            'current_water_level' => $currentLevel,
                            'current_flow_rate' => ! empty($station['discharge']) ? (float) $station['discharge'] : $riverLevel->current_flow_rate,
                            'last_updated' => Carbon::now(),
                            'weekly_data' => $weeklyData,
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
}
