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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Post office name
            $table->string('code')->unique(); // Post office code (e.g., "CO001", "GPO", etc.)
            $table->string('address'); // Full address
            $table->string('city'); // City
            $table->string('province'); // Province
            $table->string('postal_code'); // Postal code
            $table->string('phone')->nullable(); // Contact phone
            $table->boolean('is_active')->default(true); // Status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
