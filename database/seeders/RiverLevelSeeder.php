<?php

namespace Database\Seeders;

use App\Models\RiverLevel;
use Illuminate\Database\Seeder;

class RiverLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RiverLevel::create([
            'river_name' => 'Bagmati River',
            'lat' => 27.6515,
            'lng' => 85.2872,
            'level' => 3.2,
            'threshold' => 4.0,
        ]);

        RiverLevel::create([
            'river_name' => 'Bishnumati River',
            'lat' => 27.69162,
            'lng' => 85.30194,
            'level' => 2.8,
            'threshold' => 3.5,
        ]);

        RiverLevel::create([
            'river_name' => 'Manohara River',
            'lat' => 27.66818,
            'lng' => 85.35206,
            'level' => 2.5,
            'threshold' => 3.0,
        ]);
    }
}
