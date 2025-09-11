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
            $table->string('id')->primary();
            $table->string('name');
            $table->string('country');
            $table->string('continent');
            $table->integer('length');
            $table->float('current_water_level');
            $table->float('normal_water_level');
            $table->string('status');
            $table->float('current_flow_rate');
            $table->float('average_flow_rate');
            $table->float('temperature');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->text('description');
            $table->timestamp('last_updated');
            $table->json('weekly_data');
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
