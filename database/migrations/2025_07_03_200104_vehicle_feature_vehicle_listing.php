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
        Schema::create('vehicle_feature_vehicle_listing', function (Blueprint $table) {
            $table->id(); // optional, can be omitted if no extra fields
            $table->foreignId('vehicle_feature_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_listing_id')->constrained()->onDelete('cascade');
            $table->timestamps(); // optional
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_feature_vehicle_listing');
    }
};
