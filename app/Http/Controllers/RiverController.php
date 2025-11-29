<?php

namespace App\Http\Controllers;

use App\Models\RiverLevel;
use App\Services\DhmScraperService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RiverController extends Controller
{
    public function index()
    {
        // Cache DHM data for 15 minutes (900 seconds) to avoid frequent scraping
        $cacheKey = 'dhm_river_data';
        $cacheDuration = 900; // 15 minutes

        $scraper = new DhmScraperService;
        $isFromCache = Cache::has($cacheKey);

        $dhmData = Cache::remember($cacheKey, $cacheDuration, function () use ($scraper) {
            Log::info('Fetching fresh DHM data (cache expired or missing)');

            $data = $scraper->fetch();

            Log::info('DHM Scraper called in RiverController::index', [
                'timestamp' => now()->toISOString(),
                'data_count' => count($data),
                'sample_data' => array_slice($data, 0, 3), // Log first 3 records as sample
                'url' => 'https://dhm.gov.np/hydrology/realtime-stream',
                'cached' => false,
            ]);

            return $data;
        });

        if ($isFromCache) {
            Log::info('Using cached DHM data', [
                'data_count' => count($dhmData),
                'cache_expires_in_minutes' => 15,
            ]);
        }

        // Only update river levels if we have fresh data (not from cache)
        $updateResult = null;
        $updateCacheKey = 'dhm_data_updated_at';

        if (! $isFromCache || ! Cache::has($updateCacheKey) || Cache::get($updateCacheKey) < now()->subMinutes(15)) {
            if (! empty($dhmData)) {
                try {
                    $updateResult = $scraper->updateRiverLevels($dhmData);
                    Log::info('River levels updated from DHM data', $updateResult);

                    // Cache the update timestamp
                    Cache::put($updateCacheKey, now(), $cacheDuration);
                } catch (\Throwable $e) {
                    Log::error('Failed to update river levels from DHM data', [
                        'error' => $e->getMessage(),
                        'data_count' => count($dhmData),
                    ]);
                }
            } else {
                Log::warning('No DHM data available to update river levels');
            }
        } else {
            Log::info('Skipping river level updates - recently updated', [
                'last_updated' => Cache::get($updateCacheKey)?->toISOString(),
            ]);
        }

        $rivers = RiverLevel::with('recentMeasurements')->get();

        return Inertia::render('welcome', [
            'rivers' => $rivers,
            'dhmData' => $dhmData, // Optionally pass DHM data to the frontend
        ]);
    }

    public function show(RiverLevel $river)
    {
        // If the river has a specific scrape link, fetch data from that URL
        if (! empty($river->scrape_link)) {
            $scraper = new DhmScraperService;

            Log::info('Fetching data for specific river', [
                'river_id' => $river->id,
                'river_name' => $river->name,
                'scrape_link' => $river->scrape_link,
            ]);

            $specificData = $scraper->fetch($river->scrape_link);

            if (! empty($specificData)) {
                try {
                    // Update this specific river with the scraped data
                    $updateResult = $scraper->updateRiverLevels($specificData);
                    Log::info('Updated specific river from custom scrape link', [
                        'river_id' => $river->id,
                        'updates' => $updateResult,
                    ]);

                    // Refresh the river data
                    $river->refresh();
                } catch (\Throwable $e) {
                    Log::error('Failed to update river from custom scrape link', [
                        'river_id' => $river->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return Inertia::render('river', [
            'river' => $river->load('recentMeasurements'),
        ]);
    }
}
