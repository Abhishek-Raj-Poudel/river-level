<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('river_levels', function (Blueprint $table) {
            $table->decimal('elevation', 10, 2)->nullable()->after('scrape_link');
            $table->string('station_link')->nullable()->after('elevation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('river_levels', function (Blueprint $table) {
            $table->dropColumn(['elevation', 'station_link']);
        });
    }
};
