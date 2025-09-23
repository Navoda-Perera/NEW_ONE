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
            $table->foreignId('service_type_id')->constrained('service_types')->onDelete('cascade');
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('category', ['single_item', 'temporary_list', 'bulk_list'])->default('single_item');
            $table->integer('total_items')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('total_postage', 12, 2)->default(0);
            $table->decimal('total_commission', 12, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
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
