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
        Schema::create('post_pricing', function (Blueprint $table) {
            $table->id();
            $table->string('service_type'); // 'normal' or 'register'
            $table->decimal('min_weight', 8, 2); // Minimum weight in grams
            $table->decimal('max_weight', 8, 2); // Maximum weight in grams
            $table->decimal('price', 10, 2); // Price in LKR
            $table->timestamps();

            // Indexes for better performance
            $table->index(['service_type', 'min_weight', 'max_weight']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_pricing');
    }
};
