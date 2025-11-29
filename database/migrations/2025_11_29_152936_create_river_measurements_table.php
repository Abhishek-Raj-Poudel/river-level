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
        Schema::create('river_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('river_level_id')->constrained('river_levels')->onDelete('cascade');
            $table->decimal('water_level', 8, 2); // Water level in meters
            $table->timestamp('measured_at'); // When the measurement was taken
            $table->timestamps(); // Created/updated timestamps

            // Indexes for performance
            $table->index(['river_level_id', 'measured_at']);
            $table->index('measured_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('river_measurements');
    }
};
