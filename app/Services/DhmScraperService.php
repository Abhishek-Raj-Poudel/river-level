<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class DhmScraperService
{
    protected string $url = 'https://dhm.gov.np/hydrology/realtime-stream';
    /* protected string $url = 'https://hydrology.gov.np/#/river_watch?_k=pprrmt'; */

    public function fetch()
    {
        try {
            Log::info('Fetching DHM river data...');

            $client = new Client(['timeout' => 15, 'headers' => ['User-Agent' => 'RiverWatchScraper']]);
            $res = $client->get($this->url);
            $html = (string) $res->getBody();
            Log::info($html);

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

            Log::info('Fetched DHM data successfully', ['count' => count($rows)]);
            return $rows;
        } catch (\Throwable $e) {
            Log::error('Error fetching DHM river data', ['message' => $e->getMessage()]);
            return [];
        }
    }
}
