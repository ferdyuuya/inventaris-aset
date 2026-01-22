<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the inspections table for single-asset inspection records.
     * 
     * Purpose:
     * - Store inspection results for individual assets
     * - Track condition evaluations over time
     * - Provide audit trail for asset condition changes
     * 
     * Note: Inspection updates asset.condition ONLY.
     * It does NOT affect asset.status or asset.is_available.
     */
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->enum('condition_before', ['baik', 'rusak', 'perlu_perbaikan'])->nullable();
            $table->enum('condition_after', ['baik', 'rusak', 'perlu_perbaikan']);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('inspected_by');
            $table->timestamp('inspected_at');
            $table->timestamps();

            $table->foreign('asset_id')
                ->references('id')
                ->on('assets')
                ->onDelete('cascade');

            $table->foreign('inspected_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Index for efficient queries
            $table->index(['asset_id', 'inspected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
