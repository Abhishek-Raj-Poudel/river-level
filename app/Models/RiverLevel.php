<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Reverb\Loggers\Log;

class RiverLevel extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'country',
        'continent',
        'length',
        'current_water_level',
        'normal_water_level',
        'status',
        'current_flow_rate',
        'average_flow_rate',
        'temperature',
        'lat',
        'lng',
        'description',
        'last_updated',
        'weekly_data',
    ];

    protected $casts = [
        'weekly_data' => 'array',
        'last_updated' => 'datetime',
    ];

    protected static function booted()
    {
        static::saved(function ($river) {
            // ✅ Add some debugging
            Log::info("River saved: {$river->name}, Level: {$river->current_water_level}, Threshold: {$river->normal_water_level}");

            if ($river->current_water_level > $river->normal_water_level) {
                Log::info("🚨 River level exceeded! Broadcasting event...");

                // ✅ Use broadcast() helper instead of event()
                broadcast(new \App\Events\RiverLevelExceeded($river));

                // Alternative: You can also use event() but broadcast() is more explicit
                // event(new \App\Events\RiverLevelExceeded($river));
            }
        });
    }
}

