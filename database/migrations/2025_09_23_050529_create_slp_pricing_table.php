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
        Schema::create('slp_pricing', function (Blueprint $table) {
            $table->id();
            $table->decimal('weight_from', 8, 2); // in grams
            $table->decimal('weight_to', 8, 2); // in grams
            $table->decimal('price', 10, 2); // LKR
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slp_pricing');
    }
};
