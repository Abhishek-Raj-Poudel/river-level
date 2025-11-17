<?php

namespace App\Console\Commands;

use App\Models\RiverLevel;
use Illuminate\Console\Command;

class FixWeeklyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-weekly-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix weekly_data field to ensure it is stored as proper JSON arrays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking and fixing weekly_data for RiverLevel records...');

        $riverLevels = RiverLevel::all();
        $fixed = 0;

        foreach ($riverLevels as $riverLevel) {
            $weeklyData = $riverLevel->getRawOriginal('weekly_data'); // Get raw value from database

            // Debug: show what we have
            $this->line("Record {$riverLevel->id}: Type=".gettype($weeklyData).', Value='.substr((string) $weeklyData, 0, 50).'...');

            // If weekly_data is a string, try to decode it
            if (is_string($weeklyData)) {
                // First try to decode as-is (should work for properly stored data)
                $decoded = json_decode($weeklyData, true);
                $error = json_last_error();

                if ($error === JSON_ERROR_NONE && is_array($decoded)) {
                    // It's valid JSON array, the casting should handle it automatically
                    $this->line("✓ Valid JSON array for record ID: {$riverLevel->id}");

                    continue; // Skip to next record
                }

                // If that failed, try to decode as double-encoded JSON
                if ($error !== JSON_ERROR_NONE || ! is_array($decoded)) {
                    // The data might be double-encoded. Try decoding twice
                    if (is_string($decoded)) {
                        // First decode returned a string, try decoding again
                        $doubleDecoded = json_decode($decoded, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($doubleDecoded)) {
                            $riverLevel->weekly_data = $doubleDecoded;
                            $riverLevel->save();
                            $fixed++;
                            $this->line("Fixed double-encoded JSON for record ID: {$riverLevel->id}");

                            continue;
                        }
                    }

                    // Try removing surrounding quotes and decoding
                    $cleaned = trim($weeklyData, '"');
                    $decoded = json_decode($cleaned, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $riverLevel->weekly_data = $decoded;
                        $riverLevel->save();
                        $fixed++;
                        $this->line("Fixed quoted JSON for record ID: {$riverLevel->id}");

                        continue;
                    }
                }

                if (empty(trim($weeklyData))) {
                    // Empty string, set to empty array
                    $riverLevel->weekly_data = [];
                    $riverLevel->save();
                    $fixed++;
                    $this->line("Fixed empty string for record ID: {$riverLevel->id}");
                } else {
                    $this->warn("Could not decode weekly_data for record ID: {$riverLevel->id} - Error: ".json_last_error_msg());
                }
            } elseif (is_null($weeklyData)) {
                // Null value, set to empty array
                $riverLevel->weekly_data = [];
                $riverLevel->save();
                $fixed++;
                $this->line("Fixed null value for record ID: {$riverLevel->id}");
            }
        }

        $this->info("Fixed {$fixed} records out of {$riverLevels->count()} total records.");
    }
}
