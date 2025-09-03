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
        Schema::create('river_levels', function (Blueprint $table) {
            $table->id();
            $table->string('river_name');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->float('level');
            $table->float('threshold');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('river_levels');
    }
};
