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
                'weekly_data' => $this->generateWeeklyData($waterLevel),
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
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $weeklyData = [];

        foreach ($days as $day) {
            $variation = rand(-20, 20) / 100; // -20% to +20% variation
            $level = round($currentLevel * (1 + $variation), 2);
            $flow = round($level * rand(20, 100), 1);

            $weeklyData[] = [
                'day' => $day,
                'level' => $level,
                'flow' => $flow,
            ];
        }

        return $weeklyData;
    }

    /**
     * Fallback data in case DHM scraper fails
     */
    private function getFallbackData(): array
    {
        return [
            // Kathmandu Valley Rivers - Normal Status
            [
                'id' => Str::uuid(),
                'slug' => 'bagmati',
                'name' => 'Bagmati',
                'station_name' => 'Bagmati River at Khokana',
                'district' => 'Lalitpur',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 900,
                'current_water_level' => 35.5,
                'normal_water_level' => 40.0,
                'status' => 'normal',
                'current_flow_rate' => 85.2,
                'average_flow_rate' => 90.5,
                'temperature' => 18.5,
                'lat' => 27.6725,
                'lng' => 85.4290,
                'description' => 'The sacred Bagmati River flows through Kathmandu valley, considered holy by Hindus.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 34.2, 'flow' => 82.1],
                    ['day' => 'Tue', 'level' => 35.8, 'flow' => 86.3],
                    ['day' => 'Wed', 'level' => 33.9, 'flow' => 79.8],
                    ['day' => 'Thu', 'level' => 36.1, 'flow' => 88.7],
                    ['day' => 'Fri', 'level' => 35.5, 'flow' => 85.2],
                    ['day' => 'Sat', 'level' => 34.7, 'flow' => 83.9],
                    ['day' => 'Sun', 'level' => 35.2, 'flow' => 84.6],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'bishnumati',
                'name' => 'Bishnumati',
                'station_name' => null,
                'district' => 'Kathmandu',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 20,
                'current_water_level' => 28.3,
                'normal_water_level' => 30.0,
                'status' => 'normal',
                'current_flow_rate' => 45.8,
                'average_flow_rate' => 48.2,
                'temperature' => 17.8,
                'lat' => 27.6850,
                'lng' => 85.3200,
                'description' => 'Bishnumati River is one of the main rivers in Kathmandu valley, originating from Shivapuri.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 27.8, 'flow' => 44.2],
                    ['day' => 'Tue', 'level' => 28.9, 'flow' => 46.8],
                    ['day' => 'Wed', 'level' => 27.5, 'flow' => 43.1],
                    ['day' => 'Thu', 'level' => 29.1, 'flow' => 47.3],
                    ['day' => 'Fri', 'level' => 28.3, 'flow' => 45.8],
                    ['day' => 'Sat', 'level' => 28.0, 'flow' => 44.9],
                    ['day' => 'Sun', 'level' => 28.5, 'flow' => 46.1],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'manohara',
                'name' => 'Manohara',
                'station_name' => null,
                'district' => 'Kathmandu',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 15,
                'current_water_level' => 25.7,
                'normal_water_level' => 28.0,
                'status' => 'normal',
                'current_flow_rate' => 38.4,
                'average_flow_rate' => 41.7,
                'temperature' => 16.9,
                'lat' => 27.6500,
                'lng' => 85.3800,
                'description' => 'Manohara River flows through the heart of Kathmandu, known for its religious significance.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 25.2, 'flow' => 37.1],
                    ['day' => 'Tue', 'level' => 26.1, 'flow' => 39.3],
                    ['day' => 'Wed', 'level' => 24.8, 'flow' => 36.5],
                    ['day' => 'Thu', 'level' => 26.3, 'flow' => 40.2],
                    ['day' => 'Fri', 'level' => 25.7, 'flow' => 38.4],
                    ['day' => 'Sat', 'level' => 25.4, 'flow' => 37.8],
                    ['day' => 'Sun', 'level' => 25.9, 'flow' => 38.9],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'hanumante',
                'name' => 'Hanumante',
                'station_name' => null,
                'district' => 'Bhaktapur',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 12,
                'current_water_level' => 22.4,
                'normal_water_level' => 25.0,
                'status' => 'normal',
                'current_flow_rate' => 32.1,
                'average_flow_rate' => 35.8,
                'temperature' => 17.2,
                'lat' => 27.6200,
                'lng' => 85.3500,
                'description' => 'Hanumante River is located in the southern part of Kathmandu valley.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 21.8, 'flow' => 30.9],
                    ['day' => 'Tue', 'level' => 22.9, 'flow' => 33.2],
                    ['day' => 'Wed', 'level' => 21.5, 'flow' => 30.3],
                    ['day' => 'Thu', 'level' => 23.1, 'flow' => 34.1],
                    ['day' => 'Fri', 'level' => 22.4, 'flow' => 32.1],
                    ['day' => 'Sat', 'level' => 22.1, 'flow' => 31.7],
                    ['day' => 'Sun', 'level' => 22.6, 'flow' => 32.8],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'dhobikhola',
                'name' => 'Dhobikhola',
                'station_name' => null,
                'district' => 'Kathmandu',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 18,
                'current_water_level' => 31.2,
                'normal_water_level' => 35.0,
                'status' => 'normal',
                'current_flow_rate' => 55.7,
                'average_flow_rate' => 59.3,
                'temperature' => 18.1,
                'lat' => 27.7100,
                'lng' => 85.3100,
                'description' => 'Dhobikhola River flows through the northern part of Kathmandu valley.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 30.5, 'flow' => 53.8],
                    ['day' => 'Tue', 'level' => 31.8, 'flow' => 57.2],
                    ['day' => 'Wed', 'level' => 30.1, 'flow' => 52.9],
                    ['day' => 'Thu', 'level' => 32.1, 'flow' => 58.4],
                    ['day' => 'Fri', 'level' => 31.2, 'flow' => 55.7],
                    ['day' => 'Sat', 'level' => 30.9, 'flow' => 54.8],
                    ['day' => 'Sun', 'level' => 31.5, 'flow' => 56.3],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'tukucha',
                'name' => 'Tukucha',
                'station_name' => null,
                'district' => 'Kathmandu',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 14,
                'current_water_level' => 26.8,
                'normal_water_level' => 29.0,
                'status' => 'normal',
                'current_flow_rate' => 41.3,
                'average_flow_rate' => 44.6,
                'temperature' => 17.5,
                'lat' => 27.6800,
                'lng' => 85.2900,
                'description' => 'Tukucha River is a small river in Kathmandu valley, important for local water supply.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 26.2, 'flow' => 39.8],
                    ['day' => 'Tue', 'level' => 27.3, 'flow' => 42.7],
                    ['day' => 'Wed', 'level' => 25.9, 'flow' => 39.1],
                    ['day' => 'Thu', 'level' => 27.5, 'flow' => 43.4],
                    ['day' => 'Fri', 'level' => 26.8, 'flow' => 41.3],
                    ['day' => 'Sat', 'level' => 26.5, 'flow' => 40.6],
                    ['day' => 'Sun', 'level' => 27.0, 'flow' => 41.9],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'nakkhu',
                'name' => 'Nakkhu',
                'station_name' => null,
                'district' => 'Lalitpur',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 16,
                'current_water_level' => 24.6,
                'normal_water_level' => 27.0,
                'status' => 'normal',
                'current_flow_rate' => 36.9,
                'average_flow_rate' => 39.8,
                'temperature' => 17.0,
                'lat' => 27.6600,
                'lng' => 85.3400,
                'description' => 'Nakkhu River flows through Lalitpur district in Kathmandu valley.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 24.1, 'flow' => 35.7],
                    ['day' => 'Tue', 'level' => 25.1, 'flow' => 37.8],
                    ['day' => 'Wed', 'level' => 23.8, 'flow' => 35.1],
                    ['day' => 'Thu', 'level' => 25.3, 'flow' => 38.4],
                    ['day' => 'Fri', 'level' => 24.6, 'flow' => 36.9],
                    ['day' => 'Sat', 'level' => 24.3, 'flow' => 36.2],
                    ['day' => 'Sun', 'level' => 24.8, 'flow' => 37.5],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'balkhu',
                'name' => 'Balkhu',
                'station_name' => null,
                'district' => 'Kathmandu',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 10,
                'current_water_level' => 29.4,
                'normal_water_level' => 32.0,
                'status' => 'normal',
                'current_flow_rate' => 48.6,
                'average_flow_rate' => 52.1,
                'temperature' => 18.3,
                'lat' => 27.6900,
                'lng' => 85.3000,
                'description' => 'Balkhu River is located in the northern part of Kathmandu, important for irrigation.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 28.8, 'flow' => 46.9],
                    ['day' => 'Tue', 'level' => 29.9, 'flow' => 49.8],
                    ['day' => 'Wed', 'level' => 28.5, 'flow' => 46.2],
                    ['day' => 'Thu', 'level' => 30.1, 'flow' => 50.7],
                    ['day' => 'Fri', 'level' => 29.4, 'flow' => 48.6],
                    ['day' => 'Sat', 'level' => 29.1, 'flow' => 47.9],
                    ['day' => 'Sun', 'level' => 29.6, 'flow' => 49.1],
                ],
            ],

            // Rivers with different statuses for testing
            [
                'id' => Str::uuid(),
                'slug' => 'koshi',
                'name' => 'Koshi',
                'station_name' => null,
                'district' => 'Sunsari',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 720,
                'current_water_level' => 8.2,
                'normal_water_level' => 12.0,
                'status' => 'low',
                'current_flow_rate' => 1200.5,
                'average_flow_rate' => 1800.0,
                'temperature' => 22.1,
                'lat' => 26.6333,
                'lng' => 87.2833,
                'description' => 'Koshi River is one of the largest rivers in Nepal, prone to flooding during monsoon season.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 7.8, 'flow' => 1150.2],
                    ['day' => 'Tue', 'level' => 8.5, 'flow' => 1250.8],
                    ['day' => 'Wed', 'level' => 7.9, 'flow' => 1180.3],
                    ['day' => 'Thu', 'level' => 8.7, 'flow' => 1280.9],
                    ['day' => 'Fri', 'level' => 8.2, 'flow' => 1200.5],
                    ['day' => 'Sat', 'level' => 8.0, 'flow' => 1190.1],
                    ['day' => 'Sun', 'level' => 8.4, 'flow' => 1220.7],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'gandaki',
                'name' => 'Gandaki',
                'station_name' => null,
                'district' => 'Nawalpur',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 630,
                'current_water_level' => 18.7,
                'normal_water_level' => 15.0,
                'status' => 'high',
                'current_flow_rate' => 2100.3,
                'average_flow_rate' => 1800.0,
                'temperature' => 21.8,
                'lat' => 27.6833,
                'lng' => 83.4333,
                'description' => 'Gandaki River is a major river in Nepal, important for hydropower and irrigation.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 17.9, 'flow' => 2050.1],
                    ['day' => 'Tue', 'level' => 18.2, 'flow' => 2080.6],
                    ['day' => 'Wed', 'level' => 18.5, 'flow' => 2120.8],
                    ['day' => 'Thu', 'level' => 19.1, 'flow' => 2180.4],
                    ['day' => 'Fri', 'level' => 18.7, 'flow' => 2100.3],
                    ['day' => 'Sat', 'level' => 18.4, 'flow' => 2070.9],
                    ['day' => 'Sun', 'level' => 18.8, 'flow' => 2110.2],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'karnali',
                'name' => 'Karnali',
                'station_name' => null,
                'district' => 'Surkhet',
                'country' => 'Nepal',
                'continent' => 'Asia',
                'length' => 507,
                'current_water_level' => 25.8,
                'normal_water_level' => 18.0,
                'status' => 'critical',
                'current_flow_rate' => 3200.7,
                'average_flow_rate' => 2500.0,
                'temperature' => 23.5,
                'lat' => 28.8333,
                'lng' => 81.2000,
                'description' => 'Karnali River is the longest river in Nepal, currently experiencing critical flood levels.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 24.2, 'flow' => 3100.3],
                    ['day' => 'Tue', 'level' => 25.1, 'flow' => 3150.8],
                    ['day' => 'Wed', 'level' => 26.3, 'flow' => 3250.9],
                    ['day' => 'Thu', 'level' => 26.7, 'flow' => 3280.4],
                    ['day' => 'Fri', 'level' => 25.8, 'flow' => 3200.7],
                    ['day' => 'Sat', 'level' => 25.4, 'flow' => 3180.2],
                    ['day' => 'Sun', 'level' => 26.1, 'flow' => 3230.5],
                ],
            ],

            // International Rivers
            [
                'id' => Str::uuid(),
                'slug' => 'ganges',
                'name' => 'Ganges',
                'station_name' => null,
                'district' => 'Varanasi',
                'country' => 'India',
                'continent' => 'Asia',
                'length' => 2525,
                'current_water_level' => 12.3,
                'normal_water_level' => 15.0,
                'status' => 'normal',
                'current_flow_rate' => 8500.2,
                'average_flow_rate' => 9000.0,
                'temperature' => 24.7,
                'lat' => 25.3176,
                'lng' => 83.0059,
                'description' => 'The Ganges River is one of the most sacred rivers in Hinduism, flowing through India and Bangladesh.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 11.8, 'flow' => 8200.1],
                    ['day' => 'Tue', 'level' => 12.7, 'flow' => 8600.8],
                    ['day' => 'Wed', 'level' => 11.9, 'flow' => 8300.3],
                    ['day' => 'Thu', 'level' => 12.9, 'flow' => 8700.9],
                    ['day' => 'Fri', 'level' => 12.3, 'flow' => 8500.2],
                    ['day' => 'Sat', 'level' => 12.1, 'flow' => 8400.7],
                    ['day' => 'Sun', 'level' => 12.5, 'flow' => 8550.4],
                ],
            ],
            [
                'id' => Str::uuid(),
                'slug' => 'brahmaputra',
                'name' => 'Brahmaputra',
                'station_name' => null,
                'district' => 'Guwahati',
                'country' => 'India',
                'continent' => 'Asia',
                'length' => 3848,
                'current_water_level' => 9.8,
                'normal_water_level' => 12.0,
                'status' => 'low',
                'current_flow_rate' => 6200.4,
                'average_flow_rate' => 7500.0,
                'temperature' => 22.3,
                'lat' => 26.1445,
                'lng' => 91.7362,
                'description' => 'Brahmaputra River is one of the major rivers in Asia, flowing through Tibet, India, and Bangladesh.',
                'last_updated' => Carbon::now(),
                'weekly_data' => [
                    ['day' => 'Mon', 'level' => 9.2, 'flow' => 6000.1],
                    ['day' => 'Tue', 'level' => 10.1, 'flow' => 6300.8],
                    ['day' => 'Wed', 'level' => 9.5, 'flow' => 6100.3],
                    ['day' => 'Thu', 'level' => 10.3, 'flow' => 6400.9],
                    ['day' => 'Fri', 'level' => 9.8, 'flow' => 6200.4],
                    ['day' => 'Sat', 'level' => 9.6, 'flow' => 6150.7],
                    ['day' => 'Sun', 'level' => 10.0, 'flow' => 6250.2],
                ],
            ],
        ];

        return $fallbackData;
    }
}
