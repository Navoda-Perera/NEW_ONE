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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('receiver_name');
            $table->text('address');
            $table->enum('status', ['accept', 'dispatched', 'delivered', 'paid', 'returned', 'delete'])->default('accept');
            $table->decimal('weight', 8, 2)->nullable(); // in grams
            $table->decimal('amount', 10, 2);
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('service_type_id')->constrained('service_types')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('tracking_number')->unique()->nullable();
            $table->decimal('postage', 10, 2);
            $table->decimal('commission', 10, 2)->default(0);
            $table->foreignId('destination_post_office_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
