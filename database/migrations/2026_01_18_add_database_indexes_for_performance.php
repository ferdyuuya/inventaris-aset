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
            $table->index('code');
            $table->index('created_at');
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->index('name');
            $table->index('responsible_employee_id');
            $table->index('created_at');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->index('name');
            $table->index('email');
            $table->index('phone');
            $table->index('created_at');
        });

        // Add indexes for employees table
        Schema::table('employees', function (Blueprint $table) {
            $table->index('nik');
            $table->index('name');
            $table->index('user_id');
            $table->index('created_at');
        });

        // Add indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('name');
            $table->index('email');
            $table->index('created_at');
        });

        // Add indexes for procurements table
        Schema::table('procurements', function (Blueprint $table) {
            $table->index('name');
            $table->index('supplier_id');
            $table->index('asset_category_id');
            $table->index('location_id');
            $table->index('invoice_number');
            $table->index('procurement_date');
            $table->index('created_at');
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
            $table->dropIndex(['code']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['responsible_employee_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['phone']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['nik']);
            $table->dropIndex(['name']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('procurements', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['supplier_id']);
            $table->dropIndex(['asset_category_id']);
            $table->dropIndex(['location_id']);
            $table->dropIndex(['invoice_number']);
            $table->dropIndex(['procurement_date']);
            $table->dropIndex(['created_at']);
        });
    }
};
