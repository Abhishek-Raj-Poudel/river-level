<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Reverb\Loggers\Log;

class RiverLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'river_name',
        'lat',
        'lng',
        'level',
        'threshold',
    ];

    /* protected static function booted() */
    /* { */
    /*     static::saved(function ($river) { */
    /*         if ($river->level > $river->threshold) { */
    /*             event(new \App\Events\RiverLevelExceeded($river)); */
    /*         } */
    /*     }); */
    /* } */

protected static function booted()
    {
        static::saved(function ($river) {
            // ✅ Add some debugging
            Log::info("River saved: {$river->river_name}, Level: {$river->level}, Threshold: {$river->threshold}");

            if ($river->level > $river->threshold) {
                Log::info("🚨 River level exceeded! Broadcasting event...");

                // ✅ Use broadcast() helper instead of event()
                broadcast(new \App\Events\RiverLevelExceeded($river));

                // Alternative: You can also use event() but broadcast() is more explicit
                // event(new \App\Events\RiverLevelExceeded($river));
            }
        });
    }
}
