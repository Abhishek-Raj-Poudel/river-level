<?php

use App\Models\RiverLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

function createRiverForTest(array $overrides = []): RiverLevel
{
    return RiverLevel::create(array_merge([
        'name' => 'Koshi River',
        'country' => 'Nepal',
        'continent' => 'Asia',
        'length' => 720,
        'current_water_level' => 2.6,
        'normal_water_level' => 2.8,
        'status' => 'normal',
        'current_flow_rate' => 120.5,
        'average_flow_rate' => 98.1,
        'temperature' => 14.5,
        'lat' => 26.9,
        'lng' => 87.1,
        'description' => 'Major trans-boundary river in eastern Nepal.',
        'last_updated' => now(),
    ], $overrides));
}

test('river detail page renders the river and its recent measurements', function () {
    $river = createRiverForTest();
    $river->measurements()->create([
        'water_level' => 2.5,
        'measured_at' => now()->subDay(),
    ]);

    $this->get(route('river.show', $river))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('river')
            ->where('river.slug', $river->slug)
            ->where('river.status', 'normal')
            ->has('river.recent_measurements', 1));
});

test('river detail returns not found for an unknown slug', function () {
    $this->get('/river/does-not-exist')->assertNotFound();
});
