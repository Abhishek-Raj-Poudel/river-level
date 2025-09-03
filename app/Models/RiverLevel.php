<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
