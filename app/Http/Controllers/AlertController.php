<?php

namespace App\Http\Controllers;

use App\Models\RiverLevel;
use Illuminate\Http\Request;
/* use Illuminate\Support\Facades\Auth; */

class AlertController extends Controller
{

public function check(Request $request)
    {
        $user = $request->user();

        if (!$user->lat || !$user->lng) {
            return response()->json([
                'alert' => false,
                'message' => 'User location not set.',
                'nearby_rivers' => [],
            ]);
        }


        $nearbyRivers = RiverLevel::selectRaw("
            id, river_name, lat, lng, level, threshold,
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(lat)) *
                cos(radians(lng) - radians(?)) +
                sin(radians(?)) *
                sin(radians(lat))
            )) AS distance
        ", [$user->lat, $user->lng, $user->lat])
        ->whereRaw("(6371 * acos(
            cos(radians(?)) *
            cos(radians(lat)) *
            cos(radians(lng) - radians(?)) +
            sin(radians(?)) *
            sin(radians(lat))
        )) < 10", [$user->lat, $user->lng, $user->lat])
        ->get();

        foreach ($nearbyRivers as $river) {
            if ($river->level >= $river->threshold) {
                return response()->json([
                    'alert' => true,
                    'message' => "⚠️ Flood risk near {$river->river_name} River!",
                    'nearby_rivers' => $nearbyRivers,
                ]);
            }
        }

        return response()->json([
            'alert' => false,
            'message' => 'No flood risk nearby ✅',
            'nearby_rivers' => $nearbyRivers,
        ]);
    }
}
