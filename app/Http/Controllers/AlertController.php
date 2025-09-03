<?php

namespace App\Http\Controllers;

use App\Models\RiverLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /* public function checkAlert(Request $request) */
    /* { */
    /*     $user = Auth::user(); */
    /**/
    /*     if (!$user || !$user->lat || !$user->lng) { */
    /*         return response()->json(['message' => 'User location not available.'], 400); */
    /*     } */
    /**/
    /*     $userLat = $user->lat; */
    /*     $userLng = $user->lng; */
    /**/
    /*     // Find the closest river level entry */
    /*     $closestRiver = RiverLevel::all()->sortBy(function ($river) use ($userLat, $userLng) { */
    /*         $distance = sqrt(pow($userLat - $river->lat, 2) + pow($userLng - $river->lng, 2)); */
    /*         return $distance; */
    /*     })->first(); */
    /**/
    /*     if (!$closestRiver) { */
    /*         return response()->json(['message' => 'No river level data available.'], 404); */
    /*     } */
    /**/
    /*     $alertTriggered = $closestRiver->level > $closestRiver->threshold; */
    /**/
    /*     return response()->json([ */
    /*         'river_name' => $closestRiver->river_name, */
    /*         'river_level' => $closestRiver->level, */
    /*         'river_threshold' => $closestRiver->threshold, */
    /*         'alert_triggered' => $alertTriggered, */
    /*         'message' => $alertTriggered ? 'River level is above threshold!' : 'River level is normal.' */
    /*     ]); */
    /* } */

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
