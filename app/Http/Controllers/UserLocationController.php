<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserLocationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $user = $request->user();
        $user->update([
            'lat' => $request->lat,
            'lng' => $request->lng,
        ]);

        return back()->with('success', 'Location updated successfully.');
    }
}
