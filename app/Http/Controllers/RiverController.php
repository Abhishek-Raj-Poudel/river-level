<?php

namespace App\Http\Controllers;

use App\Models\River;
use App\Models\RiverLevel;
use App\Services\DhmScraperService;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class RiverController extends Controller
{
    public function index()
    {
        // Call DHM scraper and log the results
        $scraper = new DhmScraperService();
        $dhmData = $scraper->fetch();

        // Log the scraped data to laravel.log
        Log::info('DHM Scraper called in RiverController::index', [
            'timestamp' => now()->toISOString(),
            'data_count' => count($dhmData),
            'sample_data' => array_slice($dhmData, 0, 3), // Log first 3 records as sample
            'url' => 'https://dhm.gov.np/hydrology/realtime-stream'
        ]);

        $rivers = RiverLevel::all();

        return Inertia::render('welcome', [
            'rivers' => $rivers,
            'dhmData' => $dhmData // Optionally pass DHM data to the frontend
        ]);
    }

    public function show(RiverLevel $river)
    {
        return Inertia::render('river', [
            'river' => $river,
        ]);
    }
}
