<?php

namespace App\Http\Controllers;

use App\Models\River;
use App\Models\RiverLevel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiverController extends Controller
{
    public function index()
    {
        $rivers = River::all();

        return Inertia::render('welcome', [
            'rivers' => $rivers,
        ]);
    }

    public function show(River $river)
    {
        return Inertia::render('river', [
            'river' => $river,
        ]);
    }
}
