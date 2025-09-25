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
        Schema::create('postmen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nic', 20)->unique();
            $table->string('mobile', 15);
            $table->string('paysheet_id')->nullable();
            $table->foreignId('location_id')->constrained('locations');
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('postman_type', ['permanent', 'temporary', 'substitute']);
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postmen');
    }
};
