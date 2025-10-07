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
        Schema::create('item_bulk', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name');
            $table->enum('service_type', ['register_post', 'slp_courier', 'cod', 'remittance'])->default('register_post');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            // 'single_item' and 'temporary_list' for customer uploads, 'bulk_list' for PM bulk uploads
            $table->enum('category', ['single_item', 'temporary_list', 'bulk_list'])->default('single_item');
            $table->integer('item_quantity')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_bulk');
    }
};
