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
        Schema::table('assets', function (Blueprint $table) {
            // Add indexes for better query performance
            $table->index('asset_code');
            $table->index('name');
            $table->index('category_id');
            $table->index('location_id');
            $table->index('supplier_id');
            $table->index('status');
            $table->index('created_at');
        });

        // Add foreign key indexes if they don't exist
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['asset_code']);
            $table->dropIndex(['name']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
