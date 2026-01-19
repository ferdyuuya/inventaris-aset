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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('asset_maintenances', 'estimated_completion_date')) {
                $table->date('estimated_completion_date')->nullable()->after('maintenance_date');
            }
            if (!Schema::hasColumn('asset_maintenances', 'completed_date')) {
                $table->date('completed_date')->nullable()->after('estimated_completion_date');
            }
            if (!Schema::hasColumn('asset_maintenances', 'status')) {
                $table->enum('status', ['dalam_proses', 'selesai', 'dibatalkan'])->default('dalam_proses')->after('description');
            }
            if (!Schema::hasColumn('asset_maintenances', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status');
            }
        });

        // Add foreign key constraint for created_by if it doesn't exist
        Schema::table('asset_maintenances', function (Blueprint $table) {
            if (!Schema::hasColumn('asset_maintenances', 'created_by')) {
                return;
            }
            
            try {
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_maintenances', function (Blueprint $table) {
            if (Schema::hasColumn('asset_maintenances', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('asset_maintenances', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('asset_maintenances', 'completed_date')) {
                $table->dropColumn('completed_date');
            }
            if (Schema::hasColumn('asset_maintenances', 'estimated_completion_date')) {
                $table->dropColumn('estimated_completion_date');
            }
        });
    }
};
