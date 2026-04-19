<?php

namespace Database\Seeders;

use App\Models\RiverLevel;
use App\Services\DhmScraperService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RiverLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch real data from DHM scraper
        $dhmService = new DhmScraperService;
        $dhmData = $dhmService->fetch();

        $rivers = [];

        // Process DHM data and create river entries
        foreach ($dhmData as $station) {
            // Skip entries with empty basin or water level
            if (empty($station['basin']) || empty($station['water_level'])) {
                continue;
            }

            // Generate dummy data for missing fields
            $waterLevel = (float) $station['water_level'];
            $normalLevel = $waterLevel * (0.8 + rand(0, 40) / 100); // Normal level between 80-120% of current
            $status = $this->determineStatus($waterLevel, $normalLevel);

            // Generate coordinates based on basin (rough approximations for Nepal)
            $coordinates = $this->getCoordinatesForBasin($station['basin']);

            $slug = Str::slug($station['basin'].'-'.$station['station_name']);

            $rivers[] = [
                'slug' => $slug,
                'name' => $station['basin'], // Basin is used as river name
                'station_name' => $station['station_name'], // Station name from DHM
                'district' => $station['district'] ?? null, // District from DHM
                'elevation' => isset($station['elevation']) && is_numeric($station['elevation']) ? (float) $station['elevation'] : null,
                'station_link' => $station['station_link'] ?? null,
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => rand(50, 1000), // Dummy length in km
                'current_water_level' => $waterLevel, // Current water level from DHM
                'normal_water_level' => round($normalLevel, 2),
                'status' => $status,
                'current_flow_rate' => $waterLevel * rand(20, 100), // Dummy flow rate based on water level
                'average_flow_rate' => $waterLevel * rand(15, 80), // Dummy average flow
                'temperature' => rand(15, 25), // Dummy temperature in Celsius
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng'],
                'description' => "River monitoring station: {$station['station_name']} in {$station['district']} district. Part of the {$station['basin']} river basin.",
                'last_updated' => Carbon::now(),
            ];
        }

        // If no DHM data available, fall back to some sample data
        if (empty($rivers)) {
            $rivers = $this->getFallbackData();
        }

        foreach ($rivers as $river) {
            // Check if record exists, if not generate ID
            $existing = RiverLevel::where('slug', $river['slug'])->first();

            if (! $existing && ! isset($river['id'])) {
                $river['id'] = (string) Str::uuid();
            }

            RiverLevel::withoutEvents(function () use ($river) {
                RiverLevel::updateOrCreate(
                    ['slug' => $river['slug']],
                    $river
                );
            });
        }

        $this->command->info('Seeded '.count($rivers).' rivers with DHM data and dummy data for missing fields');
    }

    /**
     * Determine river status based on current and normal water levels
     */
    private function determineStatus(float $current, float $normal): string
    {
        if ($normal <= 0) {
            return 'unknown';
        }

        $ratio = $current / $normal;

        if ($ratio < 0.7) {
            return 'low';
        } elseif ($ratio > 1.3) {
            return 'critical';
        } elseif ($ratio > 1.1) {
            return 'high';
        } else {
            return 'normal';
        }
    }

    /**
     * Get approximate coordinates for river basins in Nepal
     */
    private function getCoordinatesForBasin(string $basin): array
    {
        $coordinates = [
            'Koshi' => ['lat' => 26.6333, 'lng' => 87.2833],
            'Bagmati' => ['lat' => 27.6725, 'lng' => 85.4290],
            'Narayani' => ['lat' => 27.6833, 'lng' => 83.4333],
            'Karnali' => ['lat' => 28.8333, 'lng' => 81.2000],
            'Mahakali' => ['lat' => 29.0000, 'lng' => 80.5000],
            'West Rapti' => ['lat' => 27.5000, 'lng' => 83.5000],
            'Babai' => ['lat' => 28.0000, 'lng' => 81.5000],
            'Kamala' => ['lat' => 27.0000, 'lng' => 86.0000],
            'Keshaliya' => ['lat' => 26.5000, 'lng' => 87.0000],
            'Mohana' => ['lat' => 28.5000, 'lng' => 80.5000],
            'Bheri' => ['lat' => 28.5000, 'lng' => 81.5000],
            'Mechi' => ['lat' => 26.5000, 'lng' => 88.0000],
            'Kankai' => ['lat' => 26.5000, 'lng' => 87.5000],
            'Balan-Khado' => ['lat' => 26.5000, 'lng' => 86.5000],
            'Lalbakaiya' => ['lat' => 27.5000, 'lng' => 85.0000],
            'Ratu' => ['lat' => 26.8000, 'lng' => 85.9000],
        ];

        return $coordinates[$basin] ?? ['lat' => 27.7000, 'lng' => 85.3000]; // Default Kathmandu coordinates
    }

    /**
     * Generate dummy weekly data based on current water level
     */
    private function generateWeeklyData(float $currentLevel): array
    {
        $weeklyData = [];

        // Generate data for the past 7 days
        for ($i = 6; $i >= 0; $i--) {
            $variation = rand(-20, 20) / 100; // -20% to +20% variation
            $level = round($currentLevel * (1 + $variation), 2);
            $datetime = Carbon::now()->subDays($i)->startOfDay()->addHours(rand(6, 18)); // Random time between 6 AM and 6 PM

            $weeklyData[] = [
                'datetime' => $datetime->toISOString(),
                'level' => $level,
            ];
        }

        return $weeklyData;
    }

    private function getFallbackData(): array
    {
        return [];
    }
}
