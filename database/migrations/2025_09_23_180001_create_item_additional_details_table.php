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
        Schema::create('item_additional_details', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['remittance', 'insured']); // Type: remittance or insured
            $table->decimal('amount', 10, 2); // Amount
            $table->decimal('commission', 8, 2)->default(0); // Commission amount
            $table->unsignedBigInteger('created_by'); // User who created this record
            $table->unsignedBigInteger('location_id'); // Location/Post office ID
            $table->string('receiver_name'); // Recipient name
            $table->text('receiver_address'); // Recipient address
            $table->string('status')->default('pending'); // Status: pending, processed, completed
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['created_by', 'location_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_additional_details');
    }
};
