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
        Schema::table('temporary_uploads', function (Blueprint $table) {
            // Remove the service_type column
            $table->dropColumn('service_type');

            // Add the category column with enum values
            $table->enum('category', ['single_item', 'temporary_list'])->default('single_item')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('temporary_uploads', function (Blueprint $table) {
            // Add back the service_type column
            $table->enum('service_type', ['register_post', 'slp_courier', 'cod', 'remittance'])->default('register_post')->after('id');

            // Remove the category column
            $table->dropColumn('category');
        });
    }
};
