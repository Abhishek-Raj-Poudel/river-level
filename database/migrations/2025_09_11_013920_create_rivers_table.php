<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rivers', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique(); // e.g. bagmati, bishnumati
            $table->string('name');
            $table->string('country');
            $table->string('continent');
            $table->integer('length')->nullable(); // in km
            $table->float('water_level_current')->nullable();
            $table->float('water_level_normal')->nullable();
            $table->string('water_level_status')->nullable();
            $table->float('flow_rate_current')->nullable();
            $table->float('flow_rate_average')->nullable();
            $table->float('temperature')->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->json('weekly_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rivers');
    }
};
