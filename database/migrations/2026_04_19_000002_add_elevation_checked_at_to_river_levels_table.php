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
            $table->timestamp('elevation_checked_at')->nullable()->after('elevation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('river_levels', function (Blueprint $table) {
            $table->dropColumn('elevation_checked_at');
        });
    }
};
