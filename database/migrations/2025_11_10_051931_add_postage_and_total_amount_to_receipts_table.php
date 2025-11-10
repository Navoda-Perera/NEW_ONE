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
        Schema::table('receipts', function (Blueprint $table) {
            // Add new columns
            $table->decimal('item_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('postage', 12, 2)->default(0)->after('item_amount');
            $table->decimal('total_amount', 12, 2)->default(0)->after('postage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropColumn(['item_amount', 'postage', 'total_amount']);
        });
    }
};
