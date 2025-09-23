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
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Register Post, SLP Courier, COD, Remittance
            $table->string('code')->unique(); // REG_POST, SLP_COURIER, COD, REMITTANCE
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('has_weight_pricing')->default(false); // For SLP Courier
            $table->decimal('base_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
