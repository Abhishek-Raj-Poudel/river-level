<?php

namespace App\Models;

use App\Mail\RiverLevelAlertMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Reverb\Loggers\Log;

class RiverLevel extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'slug',
        'name',
        'station_name',
        'district',
        'scrape_link',
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

    /**
     * Get the measurements for the river level.
     */
    public function measurements()
    {
        return $this->hasMany(RiverMeasurement::class)->orderBy('measured_at', 'desc');
    }

    /**
     * Get recent measurements (last 7 days).
     */
    public function recentMeasurements()
    {
        return $this->measurements()->recent();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    protected static function booted()
    {
        static::creating(function ($river) {
            // Auto-generate UUID for id if not provided
            if (empty($river->id)) {
                $river->id = (string) Str::uuid();
            }

            // Auto-generate slug from name if not provided
            if (empty($river->slug)) {
                $river->slug = Str::slug($river->name);
            }
        });

        static::saved(function ($river) {
            // ✅ Add some debugging
            Log::info("River saved: {$river->name}, Level: {$river->current_water_level}, Threshold: {$river->normal_water_level}");

            if ($river->current_water_level > $river->normal_water_level) {
                Log::info('🚨 River level exceeded! Broadcasting event and sending email...');

                // ✅ Use broadcast() helper instead of event()
                broadcast(new \App\Events\RiverLevelExceeded($river));

                // Send email alert
                Mail::to('admin@example.com')->send(new RiverLevelAlertMail($river));

                // Alternative: You can also use event() but broadcast() is more explicit
                // event(new \App\Events\RiverLevelExceeded($river));
            }
        });
    }
}
