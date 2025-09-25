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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->decimal('fixed_amount', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->decimal('item_value', 10, 2);
            $table->enum('status', ['accept', 'payable', 'paid', 'delete']);
            $table->foreignId('delivered_by')->nullable()->constrained('users');
            $table->foreignId('delivered_location')->nullable()->constrained('locations');
            $table->foreignId('settlement_by')->nullable()->constrained('users');
            $table->foreignId('settlement_location')->nullable()->constrained('locations');
            $table->string('settlement_user_nic', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
