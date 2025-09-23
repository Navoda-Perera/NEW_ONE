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
        Schema::create('temporary_upload_associates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temporary_id')->constrained('temporary_uploads')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('item_value', 10, 2)->nullable();
            $table->string('sender_name');
            $table->text('receiver_address');
            $table->decimal('postage', 10, 2);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('weight', 8, 2)->nullable(); // in grams
            $table->decimal('fix_amount', 10, 2)->nullable();
            $table->string('receiver_name');
            $table->enum('status', ['accept', 'pending', 'reject'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_upload_associates');
    }
};
