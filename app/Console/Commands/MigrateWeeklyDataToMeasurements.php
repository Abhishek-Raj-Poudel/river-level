<?php

namespace App\Console\Commands;

use App\Models\RiverLevel;
use App\Models\RiverMeasurement;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MigrateWeeklyDataToMeasurements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-weekly-data-to-measurements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing weekly_data JSON to separate river_measurements table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of weekly data to measurements table...');

        $rivers = RiverLevel::whereNotNull('weekly_data')->get();
        $totalMigrated = 0;

        foreach ($rivers as $river) {
            $this->line("Processing river: {$river->name}");

            $weeklyData = $river->weekly_data ?? [];

            foreach ($weeklyData as $measurement) {
                // Handle both old format (day/level) and new format (datetime/level)
                if (isset($measurement['datetime'])) {
                    $measuredAt = Carbon::parse($measurement['datetime']);
                    $waterLevel = $measurement['level'];
                } elseif (isset($measurement['day'])) {
                    // For old format, create a datetime from the day
                    // This is a fallback - ideally data should already be migrated
                    $measuredAt = Carbon::now()->subDays(7 - $this->dayToNumber($measurement['day']));
                    $waterLevel = $measurement['level'];
                } else {
                    continue; // Skip invalid data
                }

                // Check if measurement already exists
                $existing = RiverMeasurement::where('river_level_id', $river->id)
                    ->where('measured_at', $measuredAt)
                    ->first();

                if (! $existing) {
                    RiverMeasurement::create([
                        'river_level_id' => $river->id,
                        'water_level' => $waterLevel,
                        'measured_at' => $measuredAt,
                    ]);

                    $totalMigrated++;
                }
            }
        }

        $this->info("Migration completed! Migrated {$totalMigrated} measurements.");
        $this->warn('Note: You can now remove the weekly_data column from river_levels table if desired.');
    }

    /**
     * Convert day name to number for backward compatibility.
     */
    private function dayToNumber(string $day): int
    {
        return match (strtolower($day)) {
            'mon', 'monday' => 1,
            'tue', 'tuesday' => 2,
            'wed', 'wednesday' => 3,
            'thu', 'thursday' => 4,
            'fri', 'friday' => 5,
            'sat', 'saturday' => 6,
            'sun', 'sunday' => 7,
            default => 1,
        };
    }
}
