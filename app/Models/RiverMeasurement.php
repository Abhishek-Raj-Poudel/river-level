<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiverMeasurement extends Model
{
    protected $fillable = [
        'river_level_id',
        'water_level',
        'measured_at',
    ];

    protected $casts = [
        'water_level' => 'decimal:2',
        'measured_at' => 'datetime',
    ];

    /**
     * Get the river level that owns the measurement.
     */
    public function riverLevel(): BelongsTo
    {
        return $this->belongsTo(RiverLevel::class);
    }

    /**
     * Scope to get recent measurements (last 7 days).
     */
    public function scopeRecent($query)
    {
        return $query->where('measured_at', '>=', now()->subDays(7))
            ->orderBy('measured_at', 'desc');
    }
}
