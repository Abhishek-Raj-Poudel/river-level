<?php

namespace App\Http\Controllers;

use App\Models\River;
use Inertia\Inertia;
use App\Services\DhmScraperService;
use Illuminate\Support\Facades\Cache;

class RiverController extends Controller
{
    public function index(DhmScraperService $scraper)
    {
        $rivers = River::all();
        $riversNew = Cache::remember('dhm_rivers', 300, fn() => $scraper->fetch());
        /* $riversNew = $scraper->fetch(); // scrape live data instead of DB */

        return Inertia::render('welcome', [
            'rivers' => $rivers,
            'rivers_new' => $riversNew
        ]);
    }

    public function show(River $river)
    {
        return Inertia::render('river', [
            'river' => $river,
        ]);
    }
}
