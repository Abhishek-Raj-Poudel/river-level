<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PopulateRiverSlugs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-river-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate slugs for existing rivers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rivers = \App\Models\RiverLevel::whereNull('slug')->get();

        $this->info("Found {$rivers->count()} rivers without slugs");

        foreach ($rivers as $river) {
            $slug = \Illuminate\Support\Str::slug($river->name);

            // Ensure uniqueness
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\RiverLevel::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $river->updateQuietly(['slug' => $slug]);
            $this->line("Updated {$river->name} with slug: {$slug}");
        }

        $this->info('All rivers have been updated with slugs!');
    }
}
