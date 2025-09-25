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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->integer('item_quantity');
            $table->foreignId('item_bulk_id')->constrained('item_bulk')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->string('passcode')->nullable();
            $table->enum('payment_type', ['cash', 'credit', 'online', 'prepaid']);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('location_id')->constrained('locations');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->boolean('dlt_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
