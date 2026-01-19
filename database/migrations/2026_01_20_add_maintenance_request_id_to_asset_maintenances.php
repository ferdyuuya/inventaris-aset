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
        Schema::table('asset_maintenances', function (Blueprint $table) {
            // Add foreign key to link maintenance record to the request that triggered it
            if (!Schema::hasColumn('asset_maintenances', 'maintenance_request_id')) {
                $table->unsignedBigInteger('maintenance_request_id')->nullable()->after('asset_id');
                
                $table->foreign('maintenance_request_id')
                    ->references('id')
                    ->on('maintenance_requests')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['maintenance_request_id']);
            $table->dropColumn('maintenance_request_id');
        });
    }
};
