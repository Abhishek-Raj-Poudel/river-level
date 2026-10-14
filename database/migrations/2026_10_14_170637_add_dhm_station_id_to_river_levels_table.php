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
            $table->unsignedBigInteger('dhm_station_id')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('river_levels', function (Blueprint $table) {
            $table->dropUnique(['dhm_station_id']);
            $table->dropColumn('dhm_station_id');
        });
    }
};
