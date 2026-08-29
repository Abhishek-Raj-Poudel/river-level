<?php

namespace App\Console\Commands;

use App\Services\DhmScraperService;
use Illuminate\Console\Command;

class ScrapeDhmLive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-dhm-live
                            {--channel=river_test : Socket.IO channel to fetch (river_test, rainfall_watch, river_discharge)}
                            {--json= : Optional path to dump raw rows as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch live river data from hydrology.gov.np via Socket.IO and update RiverLevel records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $channel = $this->option('channel');
        $scraper = new DhmScraperService;

        $this->info("Connecting to hydrology.gov.np (channel: {$channel})...");

        $rows = $scraper->fetchLiveData($channel);

        if (empty($rows)) {
            $this->error('No data fetched. Check connectivity or the channel name.');

            return self::FAILURE;
        }

        if ($json = $this->option('json')) {
            file_put_contents($json, json_encode($rows, JSON_PRETTY_PRINT));
            $this->info("Wrote raw rows to {$json}");
        }

        $result = $scraper->updateOrCreateRiverLevels($rows);

        $this->info("Processed: {$result['total_processed']} | Updated: {$result['updated']} | Created: {$result['created']} | Errors: ".count($result['errors']));

        foreach ($result['errors'] as $error) {
            $this->warn('Error: '.($error['station_name'] ?? 'unknown').' - '.($error['error'] ?? ''));
        }

        return self::SUCCESS;
    }
}
