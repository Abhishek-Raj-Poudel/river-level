<?php

namespace Database\Seeders;

use App\Models\RiverLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RiverLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileContent = File::get(resource_path('js/data/rivers.ts'));

        // Extract the array from the TypeScript file
        preg_match('/export const rivers: River\[\] = (?<json>.*?);/s', $fileContent, $matches);
        $jsonString = $matches['json'];

        // Clean up the string to make it valid JSON
        $jsonString = preg_replace('/(?<!")(\w+)(?=")?:/', '"$1":', $jsonString);
        $jsonString = str_replace("'", '"', $jsonString);
        $jsonString = str_replace("\n", "", $jsonString);
        $jsonString = str_replace("  ", "", $jsonString);
        $jsonString = preg_replace("/(,(\s*?))\]/", "]", $jsonString);
        $jsonString = preg_replace("/(,(\s*?))\}/", "}", $jsonString);


        $rivers = json_decode($jsonString, true);

        if (is_null($rivers)) {
            $this->command->error("Failed to decode JSON from rivers.ts");
            $this->command->info($jsonString);
            return;
        }

        foreach ($rivers as $river) {
            RiverLevel::create([
                'id' => $river['id'],
                'name' => $river['name'],
                'country' => $river['country'],
                'continent' => $river['continent'],
                'length' => $river['length'],
                'current_water_level' => $river['waterLevel']['current'],
                'normal_water_level' => $river['waterLevel']['normal'],
                'status' => $river['waterLevel']['status'],
                'current_flow_rate' => $river['flowRate']['current'],
                'average_flow_rate' => $river['flowRate']['average'],
                'temperature' => $river['temperature'],
                'lat' => $river['coordinates']['lat'],
                'lng' => $river['coordinates']['lng'],
                'description' => $river['description'],
                'last_updated' => $river['lastUpdated'],
                'weekly_data' => json_encode($river['weeklyData']),
            ]);
        }
    }
}