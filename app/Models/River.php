<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class River extends Model
{

    protected $fillable = [
        'uid',
        'name',
        'country',
        'continent',
        'length',
        'water_level_current',
        'water_level_normal',
        'water_level_status',
        'flow_rate_current',
        'flow_rate_average',
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


    public function getRouteKeyName()
    {
        return 'uid';
    }
}
