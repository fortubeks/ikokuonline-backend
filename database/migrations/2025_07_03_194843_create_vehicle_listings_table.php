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
        Schema::create('vehicle_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Seller
            $table->unsignedBigInteger('car_make_id')->nullable(); //Toyota
            $table->unsignedBigInteger('car_model_id')->nullable(); //Camry
            $table->string('slug')->unique();
            $table->enum('status', ['available', 'sold'])->default('available');
            $table->year('year')->nullable();
            $table->string('trim')->nullable();
            $table->string('color')->nullable();
            $table->string('interior_color')->nullable();
            $table->string('transmission')->nullable();
            $table->string('vin')->nullable();
            $table->string('condition')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->text('description');
            $table->string('contact_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_listings');
    }
};
