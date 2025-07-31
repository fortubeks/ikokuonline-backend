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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description');
            $table->string('slug')->unique();
            $table->decimal('price', 15, 2);
            $table->integer('stock');
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('car_make_id')->nullable(); //Toyota
            $table->unsignedBigInteger('car_model_id')->nullable(); //Camry
            $table->string('condition');
            $table->boolean('can_negotiate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
