<?php

namespace App\Http\Controllers;

use App\Models\RiverLevel;
use Inertia\Inertia;

class RiverController extends Controller
{
    public function index()
    {
        $rivers = RiverLevel::all();

        return Inertia::render('welcome', [
            'rivers' => $rivers
        ]);
    }

    public function show(River $river)
    {
        return Inertia::render('river', [
            'river' => $river,
        ]);
    }
}
